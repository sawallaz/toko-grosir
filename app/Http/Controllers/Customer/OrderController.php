<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = env('MIDTRANS_SANITIZED', true);
        Config::$is3ds = env('MIDTRANS_3DS', true);
    }

    /* ============================================================
     *  KERANJANG
     * ============================================================ */
    public function cart()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        foreach ($cart as $item) {
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

        // Cek stok
        $neededStock = $request->quantity * $unit->conversion_to_base;
        if ($product->stock_in_base_unit < $neededStock) {
            return back()->with('error', 'Stok tidak mencukupi!');
        }

        $cart = session()->get('cart', []);
        $cartKey = $product->id . '-' . $unit->id;

        $price = $unit->price; // harga normal satuan

        if (isset($cart[$cartKey])) {
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

        return back()->with('success', 'Produk masuk keranjang!');
    }

    // Hapus Item (Support Single & Bulk)
    public function removeFromCart(Request $request, $key)
    {
        $cart = session()->get('cart');

        if ($key === 'bulk') {
            // Hapus Banyak
            $keys = json_decode($request->input('keys'), true);
            if (is_array($keys)) {
                foreach ($keys as $k) {
                    unset($cart[$k]);
                }
            }
        } else {
            // Hapus Satu
            if(isset($cart[$key])) unset($cart[$key]);
        }

        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Keranjang diperbarui.');
    }



    /* ============================================================
     *  CHECKOUT (Bayar Toko / Bayar Online Midtrans)
     * ============================================================ */
    public function checkout(Request $request)
    {
        $cart = session()->get('cart');
        if (!$cart) {
            return redirect()->route('home')->with('error', 'Keranjang kosong.');
        }

        $request->validate([
            'payment_method' => 'required|in:online,store'
        ]);

        DB::beginTransaction();
        try {
            $total = 0;
            $totalItems = 0;

            foreach ($cart as $item) {
                $total += $item['price'] * $item['quantity'];
                $totalItems += $item['quantity'];
            }

            $invoice = 'ONL-' . date('ymdHis') . '-' . Auth::id();

            // SIMPAN TRANSAKSI
            $trx = Transaction::create([
                'invoice_number' => $invoice,
                'buyer_id' => Auth::id(),
                'customer_id' => null,           // Untuk transaksi online
                'total_amount' => $total,
                'total_items' => $totalItems,
                'payment_method' => $request->payment_method,
                'type' => 'online',
                'status' => 'pending',
            ]);

            // DETAIL + POTONG STOK
            foreach ($cart as $item) {
                TransactionDetail::create([
                    'transaction_id' => $trx->id,
                    'product_unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);

                // Potong stok (booking barang)
                $unit = ProductUnit::find($item['unit_id']);
                $unit->product()->decrement(
                    'stock_in_base_unit',
                    $item['quantity'] * $unit->conversion_to_base
                );
            }

            // JIKA BAYAR ONLINE → MIDTRANS
            $snapToken = null;
            if ($request->payment_method === 'online') {
                $params = [
                    'transaction_details' => [
                        'order_id' => $invoice,
                        'gross_amount' => $total,
                    ],
                    'customer_details' => [
                        'first_name' => Auth::user()->name,
                        'email' => Auth::user()->email,
                        'phone' => Auth::user()->phone,
                    ],
                ];

                $snapToken = Snap::getSnapToken($params);
                $trx->update(['snap_token' => $snapToken]);
            }

            DB::commit();
            session()->forget('cart');

            // Jika pembayaran online
            if ($request->payment_method === 'online') {
                return view('customer.order.pay', compact('trx', 'snapToken'));
            }

            return redirect()->route('orders.index')->with('success', 'Pesanan berhasil dibuat! Silakan bayar di toko.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal checkout: ' . $e->getMessage());
        }
    }


    /* ============================================================
     *  RIWAYAT ORDER & DETAIL
     * ============================================================ */
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

    /* ============================================================
     *  BATALKAN PESANAN
     * ============================================================ */
    public function cancelOrder($id)
    {
        $order = Transaction::where('buyer_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        // Kembalikan stok
        foreach ($order->details as $d) {
            $d->productUnit->product->increment(
                'stock_in_base_unit',
                $d->quantity * $d->productUnit->conversion_to_base
            );
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Pesanan berhasil dibatalkan.');
    }

    
}
