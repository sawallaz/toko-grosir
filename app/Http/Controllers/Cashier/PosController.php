<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        // Load data pendukung untuk filter kasir
        $cashiers = User::where('role', 'kasir')->orWhere('role', 'admin')->orderBy('name')->get();

        // Load pesanan online (Pending)
        $onlineOrders = Transaction::with(['customer', 'user'])
            ->where('type', 'online')
            ->where('status', 'pending')
            ->latest()
            ->paginate(10, ['*'], 'online_page');

        return view('cashier.pos.index', compact('cashiers', 'onlineOrders'));
    }

    // API Riwayat dengan Filter
    public function historyJson(Request $request)
    {
        $query = Transaction::with(['customer', 'user'])
            ->where('type', 'pos')
            ->latest();

        // 1. Filter Kasir (User ID)
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // 2. Filter Pencarian (Invoice / Pelanggan)
        if ($search = $request->q) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($c) use ($search) {
                      $c->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 3. Filter Tanggal (opsional, default hari ini)
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00', 
                $request->end_date . ' 23:59:59'
            ]);
        } else {
            // Default: tampilkan semua tanpa filter tanggal
        }

        return response()->json($query->paginate(10));
    }

    public function searchProduct(Request $request)
    {
        $q = $request->q;
        if (!$q) return response()->json([]);

        $products = Product::with(['units.unit', 'category', 'units.wholesalePrices'])
            ->where('status', 'active')
            ->where(function($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('kode_produk', 'like', "{$q}%");
            })
            ->limit(15)->get();

        $results = $products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'kode_produk' => $product->kode_produk,
                'category' => $product->category->name ?? '-',
                'image' => $product->foto_produk,
                'stock' => $product->stock_in_base_unit,
                'units' => $product->units->map(fn($pu) => [
                    'product_unit_id' => $pu->id,
                    'unit_name' => $pu->unit->name,
                    'unit_short_name' => $pu->unit->short_name,
                    'price' => $pu->price,
                    'conversion' => $pu->conversion_to_base,
                    'is_base' => $pu->is_base_unit,
                    'wholesale' => $pu->wholesalePrices->map(fn($w) => [
                        'min' => $w->min_qty,
                        'price' => $w->price
                    ])
                ])
            ];
        });

        return response()->json($results);
    }

    public function searchCustomer(Request $request)
    {
        $q = $request->q;
        $customers = Customer::where('phone', 'like', "{$q}%")
            ->orWhere('name', 'like', "%{$q}%")
            ->limit(5)
            ->get();
        return response()->json($customers);
    }

    // [BARU] CRUD CUSTOMER
    public function customerList()
    {
        return response()->json(Customer::orderBy('name')->get());
    }

    public function storeCustomerAjax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20'
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => '-'
        ]);

        return response()->json($customer);
    }

    public function updateCustomer(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20'
        ]);
        
        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone
        ]);
        
        return response()->json($customer);
    }

    public function destroyCustomer($id)
    {
        // Cek apakah sudah pernah belanja
        if(Transaction::where('customer_id', $id)->exists()) {
            return response()->json(['message' => 'Gagal: Member ini ada riwayat belanja.'], 422);
        }
        
        Customer::destroy($id);
        return response()->json(['message' => 'Member dihapus']);
    }

    // [UPDATE] Transaksi & Next Invoice
   public function store(Request $request)
{
    $request->validate([
        'cart' => 'required|array|min:1',
        'total_amount' => 'required|numeric',
        'pay_amount' => 'required|numeric',
    ]);

    // Cek jika cart kosong
    if (empty($request->cart)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Keranjang tidak boleh kosong'
        ], 422);
    }

    DB::beginTransaction();
    try {
        $invoiceNumber = $this->generateInvoiceNumber();

        // Hitung ulang total untuk memastikan consistency
        $calculatedTotal = 0;
        $totalItems = 0;
        
        foreach($request->cart as $item) {
            // Validasi setiap item
            if (empty($item['product_unit_id']) || empty($item['qty']) || empty($item['price'])) {
                throw new \Exception('Data item tidak valid');
            }
            $calculatedTotal += ($item['qty'] * $item['price']);
            $totalItems += $item['qty'];
        }

        // Cross-check total amount
        if (abs($calculatedTotal - $request->total_amount) > 1) { // Allow small floating point difference
            throw new \Exception('Total amount tidak sesuai dengan perhitungan');
        }

        $trx = Transaction::create([
            'invoice_number' => $invoiceNumber,
            'user_id' => Auth::id(),
            'customer_id' => $request->customer_id,
            'total_amount' => $calculatedTotal, // Use calculated total
            'pay_amount' => $request->pay_amount,
            'change_amount' => $request->pay_amount - $calculatedTotal,
            'total_items' => $totalItems,
            'payment_method' => $request->payment_method ?? 'cash',
            'type' => 'pos',
            'status' => 'completed'
        ]);

        foreach($request->cart as $item) {
            TransactionDetail::create([
                'transaction_id' => $trx->id,
                'product_unit_id' => $item['product_unit_id'],
                'quantity' => $item['qty'],
                'price_at_purchase' => $item['price'],
                'subtotal' => $item['qty'] * $item['price']
            ]);
            
            $pu = ProductUnit::find($item['product_unit_id']);
            if($pu) {
                $qtyBase = $item['qty'] * $pu->conversion_to_base;
                $pu->product()->decrement('stock_in_base_unit', $qtyBase);
            }
        }

        DB::commit();

        return response()->json([
            'status' => 'success', 
            'invoice' => $trx->invoice_number,
            'invoice_number' => $trx->invoice_number,
            'message' => 'Transaksi berhasil disimpan'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Transaction Error: ' . $e->getMessage());
        
        return response()->json([
            'status' => 'error', 
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

// FUNCTION GENERATE INVOICE YANG AMAN
private function generateInvoiceNumber()
{
    $prefix = 'INV-';
    $date = date('ymdHis'); // Tahun 2 digit + bulan + hari + jam + menit + detik
    
    do {
        $random = mt_rand(100, 999); // mt_rand lebih random dari rand()
        $invoiceNumber = $prefix . $date . '-' . $random;
    } while (Transaction::where('invoice_number', $invoiceNumber)->exists());
    
    return $invoiceNumber;
}

    public function printInvoice($invoice)
    {
        $transaction = Transaction::with([
            'details.productUnit.product', 
            'details.productUnit.unit', 
            'customer', 
            'user'
        ])->where('invoice_number', $invoice)->firstOrFail();
        
        return view('cashier.pos.print', compact('transaction'));
    }
}