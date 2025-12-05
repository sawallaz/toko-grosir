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
        $lastTransaction = Transaction::whereDate('created_at', today())
            ->latest()
            ->first();
        
        $lastInvoiceNumber = $lastTransaction ? $lastTransaction->invoice_number : '';
        
        $cashiers = User::where('role', 'kasir')->orWhere('role', 'admin')->orderBy('name')->get();

        return view('cashier.pos.index', compact(
            'lastInvoiceNumber',
            'cashiers'
        ));
    }

    // Method untuk mengambil pesanan online - DIPERBAIKI
    public function onlineOrdersJson(Request $request)
    {
        $query = Transaction::with([
            'customer', 
            'buyer',
            'user',
            'details' => function($q) {
                $q->with([
                    'productUnit.unit',
                    'productUnit.product' => function($p) {
                        $p->withTrashed(); // Agar produk yang dihapus tetap bisa dilihat
                    }
                ]);
            }
        ])
        ->where('type', 'online')
        ->whereIn('status', ['pending', 'process', 'ready'])
        ->orderByRaw("FIELD(status, 'pending', 'process', 'ready')")
        ->latest();
        
        if ($search = $request->q) {
            $query->where('invoice_number', 'like', "%{$search}%");
        }
        
        $orders = $query->paginate(20);
        
        // Format data untuk memastikan nama produk tampil
        $orders->getCollection()->transform(function($order) {
            foreach($order->details as $detail) {
                // Pastikan nama produk selalu ada
                if ($detail->productUnit && $detail->productUnit->product) {
                    $detail->product_name = $detail->productUnit->product->name;
                    $detail->unit_name = $detail->productUnit->unit->name ?? 'Pcs';
                } else {
                    $detail->product_name = 'Produk tidak ditemukan';
                    $detail->unit_name = 'Pcs';
                }
            }
            return $order;
        });
        
        return response()->json($orders);
    }

    // Detail pesanan - DIPERBAIKI
    public function onlineOrderDetail($id)
    {
        $trx = Transaction::with([
            'details' => function($q) {
                $q->with([
                    'productUnit.unit',
                    'productUnit.product' => function($p) {
                        $p->withTrashed();
                    }
                ]);
            },
            'customer',
            'buyer',
            'user'
        ])->findOrFail($id);
        
        // Tambahkan nama produk ke setiap detail
        foreach($trx->details as $detail) {
            if ($detail->productUnit && $detail->productUnit->product) {
                $detail->product_name = $detail->productUnit->product->name;
                $detail->unit_name = $detail->productUnit->unit->name ?? 'Pcs';
            } else {
                $detail->product_name = 'Produk tidak ditemukan';
                $detail->unit_name = 'Pcs';
            }
        }
        
        return response()->json($trx);
    }

    // Update Status Pesanan Online
    public function updateOrderStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $trx = Transaction::with('details.productUnit.product')->findOrFail($id);
            
            $request->validate([
                'status' => 'required|in:process,ready,completed,pending'
            ]);
            
            $previousStatus = $trx->status;
            $newStatus = $request->status;
            $updateData = ['status' => $newStatus];
            
            // LOGIKA STOK
            if ($previousStatus === 'pending' && $newStatus === 'process') {
                foreach($trx->details as $detail) {
                    $pu = $detail->productUnit;
                    if($pu && $pu->product) {
                        $qtyBase = $detail->quantity * $pu->conversion_to_base;
                        $currentStock = $pu->product->stock_in_base_unit;
                        
                        if ($qtyBase > $currentStock) {
                            throw new \Exception("Stok tidak mencukupi untuk: " . ($pu->product->name ?? 'Produk'));
                        }
                    }
                }
                
                // Kurangi stok
                foreach($trx->details as $detail) {
                    $pu = $detail->productUnit;
                    if($pu && $pu->product) {
                        $qtyBase = $detail->quantity * $pu->conversion_to_base;
                        $pu->product()->decrement('stock_in_base_unit', $qtyBase);
                    }
                }
            }
            
            // Jika kembali ke pending, tambah stok kembali
            if ($previousStatus === 'process' && $newStatus === 'pending') {
                foreach($trx->details as $detail) {
                    $pu = $detail->productUnit;
                    if($pu && $pu->product) {
                        $qtyBase = $detail->quantity * $pu->conversion_to_base;
                        $pu->product()->increment('stock_in_base_unit', $qtyBase);
                    }
                }
            }
            
            // Set timestamp untuk ready
            if ($newStatus === 'ready') {
                $updateData['ready_at'] = now();
            }
            
            // Set user_id jika status completed
            if ($newStatus === 'completed') {
                $updateData['user_id'] = Auth::id();
                $updateData['processed_at'] = now();
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
            'customer:id,name,phone',
            'buyer:id,name,email',  
            'user:id,name'
        ])
        ->where('status', 'completed')
        ->latest();

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

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

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00', 
                $request->end_date . ' 23:59:59'
            ]);
        }

        $transactions = $query->paginate(15);
        
        return response()->json([
            'data' => $transactions->map(function($trx) {
                return [
                    'id' => $trx->id,
                    'invoice_number' => $trx->invoice_number,
                    'created_at' => $trx->created_at,
                    'total_amount' => $trx->total_amount,
                    'customer' => $trx->customer,
                    'buyer' => $trx->buyer,
                    'user' => $trx->user
                ];
            }),
            'current_page' => $transactions->currentPage(),
            'total' => $transactions->total()
        ]);
    }

    // SEARCH PRODUCT - DIPERBAIKI DENGAN HARGA GROSIR
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
            $availableUnits = $product->units->filter(function($unit) use ($product) {
                $stockInBase = $product->stock_in_base_unit;
                $unitStock = floor($stockInBase / $unit->conversion_to_base);
                return $unitStock > 0;
            })->values();

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
                    
                    // Ambil harga grosir dan urutkan dari min_qty terbesar ke terkecil
                    $wholesalePrices = $pu->wholesalePrices->sortByDesc('min_qty')->values();
                    
                    return [
                        'product_unit_id' => $pu->id,
                        'unit_name' => $pu->unit->name,
                        'unit_short_name' => $pu->unit->short_name,
                        'price' => $pu->price,
                        'conversion' => $pu->conversion_to_base,
                        'is_base' => $pu->is_base_unit,
                        'stock_ready' => $unitStock,
                        'wholesale' => $wholesalePrices->map(function($w) {
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

    // STORE TRANSACTION - DIPERBAIKI DENGAN HARGA GROSIR
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
            $invoiceNumber = $this->generateInvoiceNumber();
            
            $calculatedTotal = 0;
            $totalItems = 0;
            
            // Hitung harga dengan memperhitungkan grosir
            foreach($request->cart as $item) {
                $productUnit = ProductUnit::with(['wholesalePrices' => function($q) {
                    $q->orderBy('min_qty', 'desc');
                }])->find($item['product_unit_id']);
                
                $hargaSatuan = $item['price'];
                
                // Cek apakah ada harga grosir yang berlaku
                if ($productUnit && $productUnit->wholesalePrices->isNotEmpty()) {
                    foreach ($productUnit->wholesalePrices as $wholesale) {
                        if ($item['qty'] >= $wholesale->min_qty) {
                            $hargaSatuan = $wholesale->price;
                            break;
                        }
                    }
                }
                
                $subtotal = $item['qty'] * $hargaSatuan;
                $calculatedTotal += $subtotal;
                $totalItems += $item['qty'];
                
                $pu = ProductUnit::with('product')->find($item['product_unit_id']);
                if ($pu) {
                    $stockInBase = $pu->product->stock_in_base_unit;
                    $qtyBase = $item['qty'] * $pu->conversion_to_base;
                    
                    if ($qtyBase > $stockInBase) {
                        throw new \Exception("Stok tidak mencukupi untuk produk: " . $pu->product->name);
                    }
                }
            }

            if (abs($calculatedTotal - $request->total_amount) > 100) {
                throw new \Exception("Total amount tidak sesuai!");
            }

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
                $productUnit = ProductUnit::with(['wholesalePrices' => function($q) {
                    $q->orderBy('min_qty', 'desc');
                }])->find($item['product_unit_id']);
                
                $hargaSatuan = $item['price'];
                $is_wholesale = false;
                
                // Cek apakah ada harga grosir yang berlaku
                if ($productUnit && $productUnit->wholesalePrices->isNotEmpty()) {
                    foreach ($productUnit->wholesalePrices as $wholesale) {
                        if ($item['qty'] >= $wholesale->min_qty) {
                            $hargaSatuan = $wholesale->price;
                            $is_wholesale = true;
                            break;
                        }
                    }
                }
                
                $subtotal = $item['qty'] * $hargaSatuan;
                
                TransactionDetail::create([
                    'transaction_id' => $trx->id,
                    'product_unit_id' => $item['product_unit_id'],
                    'quantity' => $item['qty'],
                    'price_at_purchase' => $hargaSatuan,
                    'subtotal' => $subtotal,
                    'is_wholesale' => $is_wholesale
                ]);
                
                $pu = ProductUnit::with('product')->find($item['product_unit_id']);
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
                'last_invoice' => $invoiceNumber
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

    // Tambahkan method ini ke dalam controller
    public function getOnlineOrderCount()
    {
        $pendingCount = Transaction::where('type', 'online')
            ->where('status', 'pending')
            ->count();
        
        $processCount = Transaction::where('type', 'online')
            ->where('status', 'process')
            ->count();
            
        $readyCount = Transaction::where('type', 'online')
            ->where('status', 'ready')
            ->count();
        
        return response()->json([
            'pending' => $pendingCount,
            'process' => $processCount,
            'ready' => $readyCount,
            'total' => $pendingCount + $processCount + $readyCount
        ]);
    }
}