<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request) {
        $productsQuery = Product::with(['category', 'baseUnit.unit'])->latest();
        if ($search = $request->input('search')) {
            $productsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('kode_produk', 'like', "%{$search}%");
            });
        }
        $products = $productsQuery->paginate(10)->withQueryString();
        return view('admin.products.index', compact('products'));
    }
    
    public function create() { 
        return redirect()->route('admin.products.index'); 
    }

    // [UPDATE] Edit Json memuat wholesalePrices
    public function editJson(Product $product)
    {
        $product->load(['units.unit', 'units.wholesalePrices']); // Load harga grosir
        return response()->json($product);
    }

    // [UPDATE] Store dengan Grosir
    public function store(Request $request)
    {
        $this->validateRequest($request);

        DB::beginTransaction();
        try {
            $fotoPath = $request->hasFile('foto_produk') ? $request->file('foto_produk')->store('products', 'public') : null;

            $product = Product::create([
                'name' => $request->name,
                'kode_produk' => $request->kode_produk,
                'foto_produk' => $fotoPath, 
                'category_id' => $request->category_id,
                'description' => $request->description,
                'status' => $request->status,
                'stock_in_base_unit' => 0, 
            ]);

            $this->saveUnits($product, $request);

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // [UPDATE] Update dengan Grosir - MENGGUNAKAN LOGIKA LAMA YANG LEBIH AMAN
    public function update(Request $request, Product $product)
    {
        $this->validateRequest($request, $product->id);

        DB::beginTransaction();
        try {
            // 1. Update Data Utama (Nama, Kode, Kategori, dll)
            $dataToUpdate = [
                'name' => $request->name,
                'kode_produk' => $request->kode_produk,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'status' => $request->status,
            ];

            if ($request->hasFile('foto_produk')) {
                if ($product->foto_produk) Storage::disk('public')->delete($product->foto_produk);
                $dataToUpdate['foto_produk'] = $request->file('foto_produk')->store('products', 'public');
            }
            
            $product->update($dataToUpdate);

            // 2. Update Satuan (Metode Cerdas: Update Existing, Create New)
            $processedUnitIds = [];

            foreach ($request->units as $index => $unitData) {
                $isBase = ((int)$request->is_base_unit_index === $index);
                $conversion = $isBase ? 1 : $unitData['conversion'];

                // Cari apakah produk ini sudah punya satuan jenis ini
                $existingUnit = $product->units()->where('unit_id', $unitData['unit_id'])->first();

                if ($existingUnit) {
                    // Jika ada, UPDATE datanya saja. ID-nya tetap, jadi riwayat AMAN.
                    $existingUnit->update([
                        'price' => $unitData['price'],
                        'conversion_to_base' => $conversion,
                        'is_base_unit' => $isBase,
                    ]);
                    
                    // Update atau buat harga grosir
                    $this->saveWholesalePrices($existingUnit, $unitData);
                    
                    $processedUnitIds[] = $existingUnit->id;
                } else {
                    // Jika belum ada, BUAT BARU.
                    $newUnit = $product->units()->create([
                        'unit_id' => $unitData['unit_id'],
                        'price' => $unitData['price'],
                        'conversion_to_base' => $conversion,
                        'is_base_unit' => $isBase,
                        'harga_beli_modal' => 0,
                    ]);
                    
                    // Simpan harga grosir untuk unit baru
                    $this->saveWholesalePrices($newUnit, $unitData);
                    
                    $processedUnitIds[] = $newUnit->id;
                }
            }

            // 3. Hapus Satuan yang Dibuang User (Safety Check)
            $unitsToDelete = $product->units()->whereNotIn('id', $processedUnitIds)->get();

            foreach ($unitsToDelete as $delUnit) {
                try {
                    // Coba hapus. Kalau ada riwayat stok, database akan menolak (Constraint).
                    $delUnit->delete();
                } catch (\Exception $e) {
                    // Jika gagal hapus karena dipakai, batalkan semua dan beri pesan jelas
                    DB::rollBack();
                    return back()->withInput()->with('error', 'GAGAL UPDATE: Anda mencoba menghapus satuan "' . ($delUnit->unit->name ?? 'Unknown') . '" yang sudah memiliki riwayat stok/penjualan. Silakan biarkan satuan tersebut, atau nonaktifkan produk jika tidak ingin dipakai.');
                }
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    // Helper Simpan Unit & Grosir untuk CREATE
    private function saveUnits($product, $request) {
        foreach ($request->units as $index => $unitData) {
            $isBase = ((int)$request->is_base_unit_index === $index);
            $conversion = $isBase ? 1 : $unitData['conversion'];

            $newUnit = $product->units()->create([
                'unit_id' => $unitData['unit_id'],
                'price' => $unitData['price'], 
                'conversion_to_base' => $conversion,
                'is_base_unit' => $isBase,
                'harga_beli_modal' => 0,
            ]);

            // Simpan Harga Grosir jika ada
            $this->saveWholesalePrices($newUnit, $unitData);
        }
    }

    // Helper untuk menyimpan harga grosir
    private function saveWholesalePrices($productUnit, $unitData) {
        // Hapus dulu semua harga grosir yang ada
        $productUnit->wholesalePrices()->delete();
        
        // Simpan yang baru jika ada
        if (isset($unitData['wholesale']) && is_array($unitData['wholesale'])) {
            foreach ($unitData['wholesale'] as $wholesale) {
                if (!empty($wholesale['min_qty']) && !empty($wholesale['price'])) {
                    $productUnit->wholesalePrices()->create([
                        'min_qty' => $wholesale['min_qty'],
                        'price' => $wholesale['price']
                    ]);
                }
            }
        }
    }

    private function validateRequest($request, $id = null) {
        $uniqueRule = 'required|string|max:100|unique:products,kode_produk' . ($id ? ",$id" : '');
        $request->validate([
            'name' => 'required|string|max:255',
            'kode_produk' => $uniqueRule,
            'category_id' => 'required',
            'status' => 'required',
            'units' => 'required|array|min:1',
            'units.*.unit_id' => 'required',
            'units.*.price' => 'required|numeric|min:0',
            // Validasi Grosir (Optional)
            'units.*.wholesale.*.min_qty' => 'nullable|integer|min:2',
            'units.*.wholesale.*.price' => 'nullable|numeric|min:0',
        ]);
    }

    public function destroy(Product $product) { 
        try { 
            $inStock = DB::table('stock_entry_details')->whereIn('product_unit_id', $product->units->pluck('id'))->exists(); 
            if ($inStock) return redirect()->route('admin.products.index')->with('error', 'Gagal Hapus: Ada riwayat stok.'); 
            if ($product->foto_produk) Storage::disk('public')->delete($product->foto_produk); 
            $product->delete(); 
            return redirect()->route('admin.products.index')->with('success', 'Dihapus.'); 
        } catch (\Exception $e) { 
            return redirect()->route('admin.products.index')->with('error', 'Gagal.'); 
        } 
    }
    
    public function toggleStatus(Product $product) { 
        $newStatus = $product->status === 'active' ? 'inactive' : 'active'; 
        $product->update(['status' => $newStatus]); 
        return response()->json(['status' => 'success']); 
    }
}