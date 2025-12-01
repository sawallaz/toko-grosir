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
        // Ambil invoice terakhir untuk hari ini
        $lastTransaction = Transaction::whereDate('created_at', today())
            ->latest()
            ->first();
        
        $lastInvoiceNumber = $lastTransaction ? $lastTransaction->invoice_number : '';
        
        // Load data kasir untuk filter
        $cashiers = User::where('role', 'kasir')->orWhere('role', 'admin')->orderBy('name')->get();

        // Hitung pesanan pending untuk badge di sidebar
        $pendingCount = Transaction::where('type', 'online')
            ->where('status', 'pending')
            ->count();

        return view('cashier.pos.index', compact(
            'lastInvoiceNumber',
            'cashiers',
            'pendingCount'
        ));
    }

    // Method untuk mengambil pesanan online berdasarkan status
    public function onlineOrdersJson(Request $request)
    {
        // Hanya tampilkan pending, process, ready
        $query = Transaction::with(['customer', 'user', 'buyer', 'details'])
            ->where('type', 'online')
            ->whereIn('status', ['pending', 'process', 'ready'])
            ->latest();
        
        // Filter pencarian
        if ($search = $request->q) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($c) use ($search) {
                      $c->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('buyer', function($b) use ($search) {
                      $b->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $orders = $query->paginate(20);
        
        return response()->json($orders);
    }

    // Ambil Detail Pesanan Online
    public function onlineOrderDetail($id)
    {
        $trx = Transaction::with(['details.productUnit.product', 'details.productUnit.unit', 'buyer', 'customer'])
            ->findOrFail($id);
        return response()->json($trx);
    }

    // Update Status Pesanan Online
    public function updateOrderStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $trx = Transaction::with('details.productUnit')->findOrFail($id);
            
            $request->validate([
                'status' => 'required|in:process,ready,completed'
            ]);
            
            $previousStatus = $trx->status;
            $updateData = ['status' => $request->status];
            
            // Jika status berubah dari pending ke process, KURANGI STOK
            if ($previousStatus === 'pending' && $request->status === 'process') {
                foreach($trx->details as $detail) {
                    $pu = $detail->productUnit;
                    if($pu) {
                        $qtyBase = $detail->quantity * $pu->conversion_to_base;
                        $pu->product()->decrement('stock_in_base_unit', $qtyBase);
                    }
                }
            }
            
            // Jika status berubah dari process ke pending, TAMBAH KEMBALI STOK
            if ($previousStatus === 'process' && $request->status === 'pending') {
                foreach($trx->details as $detail) {
                    $pu = $detail->productUnit;
                    if($pu) {
                        $qtyBase = $detail->quantity * $pu->conversion_to_base;
                        $pu->product()->increment('stock_in_base_unit', $qtyBase);
                    }
                }
            }
            
            // Set timestamp untuk ready
            if ($request->status === 'ready') {
                $updateData['ready_at'] = now();
            }
            
            // Set user_id jika status completed
            if($request->status === 'completed') {
                $updateData['user_id'] = Auth::id();
            }
            
            $trx->update($updateData);
            
            DB::commit();
            
            return response()->json([
                'status' => 'success', 
                'message' => 'Status pesanan berhasil diupdate',
                'order_status' => $trx->status
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Kasir bisa reject pesanan
    public function rejectOrder(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $trx = Transaction::findOrFail($id);
            
            if ($trx->status !== 'pending') {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Hanya pesanan pending yang bisa di-reject'
                ], 400);
            }
            
            $trx->update([
                'status' => 'cancelled',
                'user_id' => Auth::id(),
                'payment_method' => 'rejected'
            ]);
            
            DB::commit();
            
            return response()->json([
                'status' => 'success', 
                'message' => 'Pesanan berhasil direject'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Method untuk proses pesanan online
    public function processOnlineOrder(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $trx = Transaction::with(['details'])->findOrFail($id);
            
            if (!in_array($trx->status, ['pending', 'process', 'ready'])) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Pesanan tidak dapat diproses'
                ], 400);
            }
            
            if ($request->pay_amount < $trx->total_amount) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Jumlah pembayaran kurang dari total tagihan'
                ], 400);
            }
            
            // Update Status & Info Pembayaran
            $trx->update([
                'status' => 'completed',
                'user_id' => Auth::id(),
                'pay_amount' => $request->pay_amount,
                'change_amount' => $request->change_amount,
                'payment_method' => 'cash',
                'processed_at' => now()
            ]);
            
            DB::commit();
            
            return response()->json([
                'status' => 'success', 
                'message' => 'Pesanan online berhasil diselesaikan',
                'invoice_number' => $trx->invoice_number
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Process Online Order Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error', 
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function historyJson(Request $request)
{
    $query = Transaction::with([
        'customer:id,name,phone', // Ambil hanya kolom yang diperlukan
        'buyer:id,name,email',    // Ambil hanya kolom yang diperlukan  
        'user:id,name'
    ])
    ->where('status', 'completed')
    ->latest();

    // Filter Kasir
    if ($request->user_id) {
        $query->where('user_id', $request->user_id);
    }

    // Filter Pencarian
    if ($search = $request->q) {
        $query->where(function($q) use ($search) {
            $q->where('invoice_number', 'like', "%{$search}%")
            ->orWhereHas('customer', function($c) use ($search) {
                $c->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orWhereHas('buyer', function($b) use ($search) {
                $b->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        });
    }

    // Filter Tanggal
    if ($request->start_date && $request->end_date) {
        $query->whereBetween('created_at', [
            $request->start_date . ' 00:00:00', 
            $request->end_date . ' 23:59:59'
        ]);
    }

    $transactions = $query->paginate(15);
    
    // Format response
    return response()->json([
        'data' => $transactions->map(function($trx) {
            return [
                'id' => $trx->id,
                'invoice_number' => $trx->invoice_number,
                'created_at' => $trx->created_at,
                'total_amount' => $trx->total_amount,
                // Kirim data customer lengkap (object bukan array)
                'customer' => $trx->customer,
                // Kirim data buyer lengkap  
                'buyer' => $trx->buyer,
                'user' => $trx->user
            ];
        }),
        'current_page' => $transactions->currentPage(),
        'total' => $transactions->total()
    ]);
}

    // SEARCH PRODUCT
    public function searchProduct(Request $request)
    {
        $q = $request->q;
        if (!$q) return response()->json([]);

        $products = Product::with(['units.unit', 'category', 'units.wholesalePrices'])
            ->where('status', 'active')
            ->where(function($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('kode_produk', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get();

        $results = $products->map(function($product) {
            // Filter hanya unit yang memiliki stok
            $availableUnits = $product->units->filter(function($unit) use ($product) {
                $stockInBase = $product->stock_in_base_unit;
                $unitStock = floor($stockInBase / $unit->conversion_to_base);
                return $unitStock > 0;
            })->values();

            // Jika tidak ada unit dengan stok, skip produk ini
            if ($availableUnits->isEmpty()) {
                return null;
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'kode_produk' => $product->kode_produk,
                'category' => $product->category->name ?? '-',
                'stock_total' => $product->stock_in_base_unit,
                'units' => $availableUnits->map(function($pu) use ($product) {
                    $stockInBase = $product->stock_in_base_unit;
                    $unitStock = floor($stockInBase / $pu->conversion_to_base);
                    
                    return [
                        'product_unit_id' => $pu->id,
                        'unit_name' => $pu->unit->name,
                        'unit_short_name' => $pu->unit->short_name,
                        'price' => $pu->price,
                        'conversion' => $pu->conversion_to_base,
                        'is_base' => $pu->is_base_unit,
                        'stock_ready' => $unitStock,
                        'wholesale' => $pu->wholesalePrices->map(function($w) {
                            return [
                                'min' => $w->min_qty,
                                'price' => $w->price
                            ];
                        })
                    ];
                })
            ];
        })->filter()->values();

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

    // CRUD CUSTOMER
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
        if(Transaction::where('customer_id', $id)->exists()) {
            return response()->json(['message' => 'Gagal: Member ini ada riwayat belanja.'], 422);
        }
        
        Customer::destroy($id);
        return response()->json(['message' => 'Member dihapus']);
    }

    // STORE TRANSACTION (AUTO RESET SETELAH BAYAR)
    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'total_amount' => 'required|numeric',
            'pay_amount' => 'required|numeric',
            'customer_id' => 'nullable|exists:customers,id'
        ]);

        DB::beginTransaction();
        try {
            // GENERATE INVOICE NUMBER DI SERVER
            $invoiceNumber = $this->generateInvoiceNumber();
            
            // Hitung ulang total di server untuk keamanan
            $calculatedTotal = 0;
            $totalItems = 0;
            
            foreach($request->cart as $item) {
                $calculatedTotal += ($item['qty'] * $item['price']);
                $totalItems += $item['qty'];
                
                // Validasi stok
                $pu = ProductUnit::find($item['product_unit_id']);
                if ($pu) {
                    $stockInBase = $pu->product->stock_in_base_unit;
                    $qtyBase = $item['qty'] * $pu->conversion_to_base;
                    
                    if ($qtyBase > $stockInBase) {
                        throw new \Exception("Stok tidak mencukupi untuk produk: " . $pu->product->name);
                    }
                }
            }

            // Validasi total amount
            if (abs($calculatedTotal - $request->total_amount) > 100) {
                throw new \Exception("Total amount tidak sesuai!");
            }

            // Validasi pembayaran
            if ($request->pay_amount < $calculatedTotal) {
                throw new \Exception("Jumlah pembayaran kurang!");
            }

            $changeAmount = $request->pay_amount - $calculatedTotal;

            // Buat transaksi
            $trx = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => Auth::id(),
                'customer_id' => $request->customer_id,
                'total_amount' => $calculatedTotal,
                'pay_amount' => $request->pay_amount,
                'change_amount' => $changeAmount,
                'total_items' => $totalItems,
                'payment_method' => 'cash',
                'type' => 'pos',
                'status' => 'completed'
            ]);

            // Simpan detail dan kurangi stok
            foreach($request->cart as $item) {
                TransactionDetail::create([
                    'transaction_id' => $trx->id,
                    'product_unit_id' => $item['product_unit_id'],
                    'quantity' => $item['qty'],
                    'price_at_purchase' => $item['price'],
                    'subtotal' => $item['qty'] * $item['price']
                ]);
                
                // Kurangi Stok
                $pu = ProductUnit::find($item['product_unit_id']);
                if($pu) {
                    $qtyBase = $item['qty'] * $pu->conversion_to_base;
                    $pu->product()->decrement('stock_in_base_unit', $qtyBase);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success', 
                'message' => 'Transaksi berhasil',
                'invoice_number' => $invoiceNumber,
                'last_invoice' => $invoiceNumber // kirim invoice terbaru
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // FUNCTION GENERATE INVOICE NUMBER YANG AMAN
    private function generateInvoiceNumber()
    {
        $date = date('ymd');
        $time = date('His');
        $random = rand(100, 999);
        
        return 'INV-' . $date . '-' . $time . '-' . $random;
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