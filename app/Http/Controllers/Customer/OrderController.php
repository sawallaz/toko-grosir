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

    // Tambah ke Keranjang
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'unit_id' => 'required',
            'quantity' => 'required|numeric|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);
        $unit = ProductUnit::with('unit')->findOrFail($request->unit_id);
        
        // [UPDATE] Cek Stok - TAPI TIDAK KURANGI STOK DULU
        $neededStock = $request->quantity * $unit->conversion_to_base;
        if ($product->stock_in_base_unit < $neededStock) {
            return back()->with('error', 'Stok tidak mencukupi! Stok tersedia: ' . floor($product->stock_in_base_unit / $unit->conversion_to_base) . ' ' . $unit->unit->name);
        }

        $cart = session()->get('cart', []);
        
        // Key unik untuk keranjang (Produk + Satuan)
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
        return redirect()->back()->with('success', 'Produk masuk keranjang!');
    }

    // Update Quantity di Keranjang
    public function updateCart(Request $request, $key)
    {
        $cart = session()->get('cart');
        
        if(isset($cart[$key])) {
            $product = Product::find($cart[$key]['product_id']);
            $unit = ProductUnit::find($cart[$key]['unit_id']);
            
            // [UPDATE] Cek stok tapi tidak kurangi
            $neededStock = $request->quantity * $unit->conversion_to_base;
            if ($product->stock_in_base_unit < $neededStock) {
                return back()->with('error', 'Stok tidak mencukupi! Stok tersedia: ' . floor($product->stock_in_base_unit / $unit->conversion_to_base) . ' ' . $unit->unit->name);
            }
            
            $cart[$key]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
        }
        
        return redirect()->back()->with('success', 'Keranjang diperbarui!');
    }

    // Hapus dari Keranjang
    public function removeFromCart($key)
    {
        $cart = session()->get('cart');
        if(isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', $cart);
        }
        return redirect()->back()->with('success', 'Item dihapus.');
    }

    // Clear cart
    public function clearCart()
    {
        session()->forget('cart');
        return redirect()->route('cart.index')->with('success', 'Keranjang berhasil dikosongkan!');
    }

    // Checkout (Simpan Pesanan TANPA KURANGI STOK)
    public function checkout(Request $request)
    {
        $request->validate([
            'delivery_type' => 'required|in:pickup,delivery',
            'delivery_note' => 'nullable|string|max:500'
        ]);

        $cart = session()->get('cart');
        if(!$cart) return redirect()->route('home')->with('error', 'Keranjang kosong.');

        DB::beginTransaction();
        try {
            // Validasi stok sebelum checkout
            foreach($cart as $item) {
                $product = Product::find($item['product_id']);
                $unit = ProductUnit::find($item['unit_id']);
                $neededStock = $item['quantity'] * $unit->conversion_to_base;
                
                if ($product->stock_in_base_unit < $neededStock) {
                    return back()->with('error', 'Stok untuk ' . $product->name . ' tidak mencukupi! Stok tersedia: ' . floor($product->stock_in_base_unit / $unit->conversion_to_base) . ' ' . $unit->unit->name);
                }
            }

            $total = 0;
            $totalItems = 0;
            foreach($cart as $item) { 
                $total += $item['price'] * $item['quantity'];
                $totalItems += $item['quantity'];
            }

            // Ambil alamat dari user jika delivery
            $deliveryAddress = null;
            if($request->delivery_type === 'delivery') {
                // [NOTE] Anda perlu menambahkan address field di users table terlebih dahulu
                // atau gunakan alamat dari profile customer
                $deliveryAddress = Auth::user()->address ?? 'Alamat belum diatur';
            }

            $trx = Transaction::create([
                'invoice_number' => 'ONL-' . date('ymdHis') . '-' . Auth::id(),
                'buyer_id' => Auth::id(),
                'customer_id' => null,
                'total_amount' => $total,
                'total_items' => $totalItems,
                'payment_method' => 'transfer',
                'type' => 'online',
                'status' => 'pending',
                'delivery_type' => $request->delivery_type,
                'delivery_address' => $deliveryAddress,
                'delivery_note' => $request->delivery_note
            ]);

            foreach($cart as $item) {
                TransactionDetail::create([
                    'transaction_id' => $trx->id,
                    'product_unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price']
                ]);
            }

            DB::commit();
            session()->forget('cart');

            return redirect()->route('orders.index')->with('success', 'Pesanan berhasil dibuat! Mohon tunggu konfirmasi kasir.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal checkout: ' . $e->getMessage());
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
}