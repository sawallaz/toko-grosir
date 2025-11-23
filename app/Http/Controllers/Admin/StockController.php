<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\StockEntry;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = StockEntry::with(['user', 'supplier', 'details.productUnit.product', 'details.productUnit.unit'])
            ->latest();

        if ($search = $request->input('search_history')) {
            $query->where(function($q) use ($search) {
                // 1. Cari No Transaksi
                $q->where('transaction_number', 'like', "%{$search}%")
                  // 2. Cari Nama Supplier
                  ->orWhereHas('supplier', function($s) use ($search) {
                      $s->where('name', 'like', "%{$search}%");
                  })
                  // 3. Cari Nama Pembuat (User)
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  })
                  // 4. [BARU] Cari Nama Produk di dalam Detail
                  ->orWhereHas('details.productUnit.product', function($p) use ($search) {
                      $p->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('entry_date', [$request->start_date, $request->end_date]);
        }

        $stockHistory = $query->paginate(10)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('admin.stock.index', compact('stockHistory', 'suppliers', 'users'));
    }

    public function searchProduct(Request $request)
    {
        $query = $request->get('q');
        if (!$query) return response()->json([]);

        $products = Product::with(['units.unit'])
            ->where('name', 'like', "%{$query}%")
            ->orWhere('kode_produk', 'like', "%{$query}%")
            ->limit(10)->get();

        $results = $products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'kode_produk' => $product->kode_produk,
                'stock_in_base_unit' => $product->stock_in_base_unit,
                'units' => $product->units->map(function($pu) {
                    return [
                        'product_unit_id' => $pu->id,
                        'unit_id' => $pu->unit_id,
                        'name' => $pu->unit->name,
                        'short_name' => $pu->unit->short_name,
                        'price' => $pu->price, 
                        'harga_beli_modal' => $pu->harga_beli_modal, 
                        'conversion' => $pu->conversion_to_base,
                        'is_base' => $pu->is_base_unit
                    ];
                })
            ];
        });

        return response()->json($results);
    }

    public function storeSupplierAjax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20'
        ]);
        
        $supplier = Supplier::create([
            'name' => $request->name, 
            'phone' => $request->phone ?? '-'
        ]);
        
        return response()->json($supplier);
    }

    public function editJson($id)
    {
        $entry = StockEntry::with(['details.productUnit.product.units.unit'])->findOrFail($id);
        return response()->json($entry);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        DB::beginTransaction();
        try {
            $this->saveStock($request);
            DB::commit();
            return redirect()->route('admin.stok.index')->with('success', 'Stok berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock store error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        // [FIX] Validasi yang lebih aman untuk update
        $request->validate([
            'entry_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.product_unit_id' => 'required|exists:product_units,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $stockEntry = StockEntry::with('details')->findOrFail($id);
            
            // 1. Rollback Stok Lama
            foreach($stockEntry->details as $detail) {
                $pu = ProductUnit::find($detail->product_unit_id);
                if($pu) {
                    $qtyBase = $detail->quantity * $pu->conversion_to_base;
                    $pu->product()->decrement('stock_in_base_unit', $qtyBase);
                }
            }
            $stockEntry->details()->delete();

            // 2. Update Header
            $totalValue = 0;
            foreach ($request->items as $item) {
                $totalValue += $item['quantity'] * $item['price'];
            }
            
            $supplierId = $request->supplier_id;
            if (empty($supplierId) || $supplierId === "null") $supplierId = null;

            $stockEntry->update([
                'user_id' => $request->user_id,
                'supplier_id' => $supplierId,
                'entry_date' => $request->entry_date,
                'total_value' => $totalValue,
                'notes' => $request->notes,
            ]);

            // 3. Insert Detail Baru & Update Stok
            foreach ($request->items as $item) {
                $stockEntry->details()->create([
                    'product_unit_id' => $item['product_unit_id'],
                    'quantity' => $item['quantity'],
                    'price_at_entry' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);

                $productUnit = ProductUnit::find($item['product_unit_id']);
                if ($productUnit) {
                    $stockToAdd = $item['quantity'] * $productUnit->conversion_to_base;
                    $productUnit->product()->increment('stock_in_base_unit', $stockToAdd);
                    
                    // Update harga beli modal hanya jika lebih besar dari 0
                    if ($item['price'] > 0) {
                        $productUnit->update(['harga_beli_modal' => $item['price']]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.stok.index')->with('success', 'Riwayat stok berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock update error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $stockEntry = StockEntry::with('details')->findOrFail($id);
            foreach($stockEntry->details as $detail) {
                $pu = ProductUnit::find($detail->product_unit_id);
                if($pu) {
                    $qtyBase = $detail->quantity * $pu->conversion_to_base;
                    $pu->product()->decrement('stock_in_base_unit', $qtyBase);
                }
            }
            $stockEntry->details()->delete();
            $stockEntry->delete();
            
            DB::commit();
            return redirect()->route('admin.stok.index')->with('success', 'Transaksi stok berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock delete error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    private function validateRequest($request) {
        $request->validate([
            'entry_date' => 'required|date',
            'user_id' => 'required',
            'items' => 'required|array|min:1',
            'items.*.product_unit_id' => 'required',
            // Batasi angka agar tidak error overflow di database
            'items.*.quantity' => 'required|integer|min:1|max:1000000', // Maks 1 juta item per baris
            'items.*.price' => 'required|numeric|min:0|max:99999999999', // Maks 99 Milyar
        ], [
            'items.*.quantity.max' => 'Jumlah stok terlalu besar (Maks 1.000.000)',
            'items.*.price.max' => 'Harga beli terlalu besar',
        ]);
    }

    private function saveStock($request, $trxNumber = null) {
        $totalValue = 0;
        foreach ($request->items as $item) {
            $totalValue += $item['quantity'] * $item['price'];
        }
        
        $supplierId = ($request->supplier_id == "null" || empty($request->supplier_id)) ? null : $request->supplier_id;

        $stockEntry = StockEntry::create([
            'transaction_number' => $trxNumber ?? 'STK-' . date('ymdHis'),
            'user_id' => $request->user_id,
            'supplier_id' => $supplierId,
            'entry_date' => $request->entry_date,
            'total_value' => $totalValue,
            'notes' => $request->notes,
        ]);

        foreach ($request->items as $item) {
            $stockEntry->details()->create([
                'product_unit_id' => $item['product_unit_id'],
                'quantity' => $item['quantity'],
                'price_at_entry' => $item['price'],
                'subtotal' => $item['quantity'] * $item['price'],
            ]);

            $productUnit = ProductUnit::find($item['product_unit_id']);
            if ($productUnit) {
                $stockToAdd = $item['quantity'] * $productUnit->conversion_to_base;
                $productUnit->product()->increment('stock_in_base_unit', $stockToAdd);
                
                // Update harga beli modal hanya jika lebih besar dari 0
                if ($item['price'] > 0) {
                    $productUnit->update(['harga_beli_modal' => $item['price']]);
                }
            }
        }
    }
}