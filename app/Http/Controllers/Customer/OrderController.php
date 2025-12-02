<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Helpers\MidtransHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // Tampilkan Keranjang
    public function cart()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        foreach($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return view('customer.order.cart', compact('cart', 'total'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'unit_id' => 'required',
            'quantity' => 'required|numeric|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);
        $unit = ProductUnit::with('unit')->findOrFail($request->unit_id);
        
        // Cek Stok
        $neededStock = $request->quantity * $unit->conversion_to_base;
        if ($product->stock_in_base_unit < $neededStock) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi! Stok tersedia: ' . floor($product->stock_in_base_unit / $unit->conversion_to_base) . ' ' . $unit->unit->name
            ]);
        }

        $cart = session()->get('cart', []);
        $cartKey = $product->id . '-' . $unit->id;

        // Cek Harga Grosir
        $price = $unit->price;
        if($unit->wholesalePrices && count($unit->wholesalePrices) > 0) {
            $wholesalePrice = $unit->wholesalePrices->sortByDesc('min_qty')->firstWhere('min_qty', '<=', $request->quantity);
            if($wholesalePrice) {
                $price = $wholesalePrice->price;
            }
        }

        if(isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $request->quantity;
        } else {
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_id' => $unit->id,
                'unit_name' => $unit->unit->name,
                'price' => $price,
                'quantity' => $request->quantity,
                'image' => $product->foto_produk,
                'conversion' => $unit->conversion_to_base
            ];
        }

        session()->put('cart', $cart);
        
        $cartCount = collect($cart)->sum('quantity');
        
        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang',
            'cartCount' => $cartCount
        ]);
    }


    // AJAX: Update Cart Quantity
    public function updateCart(Request $request, $key)
    {
        $cart = session()->get('cart');
        
        if(!isset($cart[$key])) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak ditemukan'
            ]);
        }
        
        $request->validate([
            'quantity' => 'required|numeric|min:1'
        ]);
        
        $item = $cart[$key];
        $product = Product::find($item['product_id']);
        $unit = ProductUnit::find($item['unit_id']);
        
        // Cek stok
        $neededStock = $request->quantity * $unit->conversion_to_base;
        if ($product->stock_in_base_unit < $neededStock) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi! Stok tersedia: ' . floor($product->stock_in_base_unit / $unit->conversion_to_base) . ' ' . $unit->unit->name
            ]);
        }
        
        $cart[$key]['quantity'] = $request->quantity;
        session()->put('cart', $cart);
        
        // Recalculate
        $subtotal = $cart[$key]['price'] * $request->quantity;
        $total = collect($cart)->sum(function($item) {
            return $item['price'] * $item['quantity'];
        });
        $itemCount = count($cart);
        
        return response()->json([
            'success' => true,
            'subtotal' => $subtotal,
            'total' => $total,
            'itemCount' => $itemCount
        ]);
    }

       // AJAX: Remove from Cart
    public function removeFromCart($key)
    {
        $cart = session()->get('cart');
        
        if(!isset($cart[$key])) {
            return response()->json(['success' => false]);
        }
        
        unset($cart[$key]);
        session()->put('cart', $cart);
        
        $total = collect($cart)->sum(function($item) {
            return $item['price'] * $item['quantity'];
        });
        $itemCount = count($cart);
        
        return response()->json([
            'success' => true,
            'total' => $total,
            'itemCount' => $itemCount
        ]);
    }

    // Clear cart
    public function clearCart()
    {
        session()->forget('cart');
        return redirect()->route('cart.index')->with('success', 'Keranjang berhasil dikosongkan!');
    }

     public function checkout(Request $request)
    {
        $request->validate([
            'delivery_type' => 'required|in:pickup,delivery',
            'delivery_note' => 'nullable|string|max:500',
            'delivery_address' => 'required_if:delivery_type,delivery|string|max:500',
            'customer_phone' => 'required|string|max:20'
        ]);

        $cart = session()->get('cart');
        if(!$cart) {
            return redirect()->route('home')->with('error', 'Keranjang kosong.');
        }

        DB::beginTransaction();
        try {
            // Validasi stok
            foreach($cart as $item) {
                $product = Product::find($item['product_id']);
                $unit = ProductUnit::find($item['unit_id']);
                $neededStock = $item['quantity'] * $unit->conversion_to_base;
                
                if ($product->stock_in_base_unit < $neededStock) {
                    return back()->with('error', 'Stok untuk ' . $product->name . ' tidak mencukupi!');
                }
            }

            $total = 0;
            $totalItems = 0;
            $items = [];
            
            foreach($cart as $item) { 
                $total += $item['price'] * $item['quantity'];
                $totalItems += $item['quantity'];
                
                // Prepare items untuk Midtrans
                $items[] = [
                    'id' => $item['product_id'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'name' => $item['product_name'],
                    'brand' => 'FADLIMART',
                    'category' => 'Grosir',
                    'merchant_name' => 'FADLIMART'
                ];
            }

            // Tambah biaya pengiriman jika delivery
            $shippingCost = 0;
            if($request->delivery_type === 'delivery') {
                $shippingCost = 10000;
                $total += $shippingCost;
                
                // Add shipping as item
                $items[] = [
                    'id' => 'SHIPPING',
                    'price' => $shippingCost,
                    'quantity' => 1,
                    'name' => 'Biaya Pengiriman',
                    'category' => 'Shipping',
                    'merchant_name' => 'FADLIMART'
                ];
            }

            // Generate unique order ID
            $orderId = 'FADLI-' . date('Ymd') . '-' . Str::random(8);

            // Simpan transaksi
            $transaction = Transaction::create([
                'invoice_number' => $orderId,
                'midtrans_order_id' => $orderId,
                'buyer_id' => Auth::id(),
                'total_amount' => $total,
                'total_items' => $totalItems,
                'payment_method' => 'midtrans',
                'payment_status' => 'pending',
                'type' => 'online',
                'status' => 'pending',
                'delivery_type' => $request->delivery_type,
                'delivery_address' => $request->delivery_type === 'delivery' ? $request->delivery_address : null,
                'delivery_note' => $request->delivery_note,
                'expired_at' => now()->addHours(24)
            ]);

            // Simpan detail transaksi
            foreach($cart as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price']
                ]);
            }

            // Generate Snap Token untuk Midtrans
            $user = Auth::user();
            $orderData = [
                'order_id' => $orderId,
                'gross_amount' => $total,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $request->customer_phone,
                'items' => $items,
                'finish_url' => route('payment.finish', ['order_id' => $orderId]),
                'error_url' => route('payment.error', ['order_id' => $orderId]),
                'pending_url' => route('payment.pending', ['order_id' => $orderId]),
            ];

            // Add shipping address if delivery
            if($request->delivery_type === 'delivery') {
                $orderData['shipping_address'] = [
                    'first_name' => $user->name,
                    'address' => $request->delivery_address,
                    'city' => 'Kota',
                    'postal_code' => '00000',
                    'phone' => $request->customer_phone,
                    'country_code' => 'IDN'
                ];
            }

            $snapToken = MidtransHelper::createSnapToken($orderData);

            // Update transaksi dengan snap token
            $transaction->update([
                'midtrans_snap_token' => $snapToken
            ]);

            DB::commit();
            session()->forget('cart');

            // Redirect ke halaman pembayaran
            return redirect()->route('payment.show', ['order_id' => $orderId])
                            ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkout Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal checkout: ' . $e->getMessage());
        }
    }

     // Show Payment Page
    public function showPayment($orderId)
    {
        $transaction = Transaction::where('midtrans_order_id', $orderId)
            ->where('buyer_id', Auth::id())
            ->firstOrFail();

        if ($transaction->payment_status === 'paid') {
            return redirect()->route('orders.show', $transaction->id)
                ->with('info', 'Pesanan ini sudah dibayar.');
        }

        if ($transaction->expired_at && $transaction->expired_at->isPast()) {
            return redirect()->route('orders.index')
                ->with('error', 'Pesanan sudah kedaluwarsa.');
        }

        return view('customer.order.payment', compact('transaction'));
    }

    // Payment Finish Page
    public function paymentFinish($orderId)
    {
        $transaction = Transaction::where('midtrans_order_id', $orderId)
            ->where('buyer_id', Auth::id())
            ->firstOrFail();

        return view('customer.order.payment-finish', compact('transaction'));
    }

    // Payment Error Page
    public function paymentError($orderId)
    {
        return view('customer.order.payment-error', compact('orderId'));
    }

    // Payment Pending Page
    public function paymentPending($orderId)
    {
        $transaction = Transaction::where('midtrans_order_id', $orderId)
            ->where('buyer_id', Auth::id())
            ->firstOrFail();

        return view('customer.order.payment-pending', compact('transaction'));
    }

    // Midtrans Notification Handler (Webhook)
    public function paymentNotification(Request $request)
    {
        try {
            $notification = MidtransHelper::handleNotification();
            
            if (!$notification) {
                return response()->json(['status' => 'error', 'message' => 'Invalid notification']);
            }

            $transaction = Transaction::where('midtrans_order_id', $notification['order_id'])->first();
            
            if (!$transaction) {
                return response()->json(['status' => 'error', 'message' => 'Transaction not found']);
            }

            DB::beginTransaction();

            // Update berdasarkan status transaksi
            $transactionStatus = $notification['status'];
            $fraudStatus = $notification['fraud_status'];

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $transaction->update([
                        'payment_status' => 'challenge',
                        'status' => 'pending',
                        'midtrans_transaction_id' => $notification['transaction_id'],
                        'midtrans_response' => json_encode($notification['raw'])
                    ]);
                } else if ($fraudStatus == 'accept') {
                    $transaction->update([
                        'payment_status' => 'paid',
                        'status' => 'process',
                        'paid_at' => now(),
                        'midtrans_transaction_id' => $notification['transaction_id'],
                        'midtrans_response' => json_encode($notification['raw'])
                    ]);
                    
                    // Kurangi stok produk
                    $this->reduceStock($transaction);
                }
            } else if ($transactionStatus == 'settlement') {
                $transaction->update([
                    'payment_status' => 'paid',
                    'status' => 'process',
                    'paid_at' => now(),
                    'midtrans_transaction_id' => $notification['transaction_id'],
                    'midtrans_response' => json_encode($notification['raw'])
                ]);
                
                $this->reduceStock($transaction);
            } else if ($transactionStatus == 'pending') {
                $transaction->update([
                    'payment_status' => 'pending',
                    'midtrans_transaction_id' => $notification['transaction_id'],
                    'midtrans_response' => json_encode($notification['raw'])
                ]);
            } else if ($transactionStatus == 'deny' || 
                      $transactionStatus == 'expire' || 
                      $transactionStatus == 'cancel') {
                $transaction->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled',
                    'midtrans_transaction_id' => $notification['transaction_id'],
                    'midtrans_response' => json_encode($notification['raw'])
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment Notification Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Helper: Kurangi stok
    private function reduceStock($transaction)
    {
        $details = $transaction->details;
        
        foreach($details as $detail) {
            $productUnit = $detail->productUnit;
            $product = $productUnit->product;
            
            $neededStock = $detail->quantity * $productUnit->conversion_to_base;
            
            if ($product->stock_in_base_unit >= $neededStock) {
                $product->decrement('stock_in_base_unit', $neededStock);
            }
        }
    }

    // Check Payment Status
    public function checkPaymentStatus($orderId)
    {
        $transaction = Transaction::where('midtrans_order_id', $orderId)
            ->where('buyer_id', Auth::id())
            ->firstOrFail();

        try {
            $status = MidtransHelper::getStatus($orderId);
            
            if ($status && $status->transaction_status) {
                // Update local status
                $transaction->update([
                    'payment_status' => $status->transaction_status,
                    'midtrans_response' => json_encode($status)
                ]);
                
                // Jika sudah paid, update stock
                if ($status->transaction_status == 'settlement' || 
                    ($status->transaction_status == 'capture' && $status->fraud_status == 'accept')) {
                    $this->reduceStock($transaction);
                }
            }
            
            return response()->json([
                'status' => $transaction->payment_status,
                'paid' => in_array($transaction->payment_status, ['paid', 'settlement', 'capture']),
                'order_id' => $transaction->id
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Check Payment Status Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to check status'], 500);
        }
    }

     // [BARU] Tampilkan form checkout dengan pilihan pengiriman
    public function showCheckoutForm()
    {
        $cart = session()->get('cart', []);
        if(!$cart) return redirect()->route('home')->with('error', 'Keranjang kosong.');

        $total = 0;
        foreach($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return view('customer.order.checkout', compact('cart', 'total'));
    }

    // [BARU] Cancel Pesanan oleh Customer (hanya untuk status pending)
    public function cancelOrder($id)
    {
        DB::beginTransaction();
        try {
            $order = Transaction::where('buyer_id', Auth::id())
                ->where('id', $id)
                ->where('status', 'pending') // Hanya bisa cancel yang masih pending
                ->firstOrFail();

            $order->update([
                'status' => 'cancelled'
            ]);

            DB::commit();

            return redirect()->route('orders.index')->with('success', 'Pesanan berhasil dibatalkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('orders.index')->with('error', 'Gagal membatalkan pesanan: ' . $e->getMessage());
        }
    }

    // Riwayat Pesanan Saya
    public function index()
    {
        $orders = Transaction::where('buyer_id', Auth::id())
            ->where('type', 'online')
            ->latest()
            ->get();
            
        return view('customer.order.index', compact('orders'));
    }
    
    public function show($id)
    {
        $order = Transaction::where('buyer_id', Auth::id())
            ->with('details.productUnit.product')
            ->findOrFail($id);
        return view('customer.order.show', compact('order'));
    }

    // Quick View Product
    public function quickView($id)
    {
        $product = Product::with(['category', 'units.unit', 'units.wholesalePrices', 'baseUnit.unit'])
            ->where('status', 'active')
            ->findOrFail($id);
            
        return view('customer.partials.quick-view', compact('product'));
    }

    // AJAX: Load More Products
    public function loadMore(Request $request)
    {
        $query = Product::with(['category', 'baseUnit.unit', 'units'])
            ->where('status', 'active')
            ->whereHas('baseUnit');

        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Sorting
        if ($request->sort == 'price_low') {
            $query->orderByRaw('(SELECT price FROM product_units WHERE product_id = products.id AND is_base_unit = 1) ASC');
        } elseif ($request->sort == 'price_high') {
            $query->orderByRaw('(SELECT price FROM product_units WHERE product_id = products.id AND is_base_unit = 1) DESC');
        } elseif ($request->sort == 'stock') {
            $query->orderBy('stock_in_base_unit', 'DESC');
        } else {
            $query->latest();
        }

        $products = $query->paginate(20);
        
        if ($request->ajax()) {
            return view('customer.partials.products-grid', compact('products'))->render();
        }
        
        return redirect()->route('home');
    }

}