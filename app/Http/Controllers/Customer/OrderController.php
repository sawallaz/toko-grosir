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
        
        // Cek Stok
        $neededStock = $request->quantity * $unit->conversion_to_base;
        if ($product->stock_in_base_unit < $neededStock) {
            return back()->with('error', 'Stok tidak mencukupi!');
        }

        $cart = session()->get('cart', []);
        
        // Key unik untuk keranjang (Produk + Satuan)
        $cartKey = $product->id . '-' . $unit->id;

        // Cek Harga Grosir (Simple Logic)
        $price = $unit->price;
        // (Opsional: Tambahkan logika cek wholesale_prices di sini jika mau harga berubah di cart)

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

    // Checkout (Simpan Pesanan)
    public function checkout()
    {
        $cart = session()->get('cart');
        if(!$cart) return redirect()->route('home')->with('error', 'Keranjang kosong.');

        DB::beginTransaction();
        try {
            $total = 0;
            foreach($cart as $item) { $total += $item['price'] * $item['quantity']; }

            $trx = Transaction::create([
                'invoice_number' => 'ONL-' . date('ymdHis') . '-' . Auth::id(),
                'buyer_id' => Auth::id(), // [PENTING] Simpan ID User yang login
                'customer_id' => null,    // Kosongkan customer_id manual
                'total_amount' => $total,
                'total_items' => count($cart),
                'payment_method' => 'transfer',
                'type' => 'online',
                'status' => 'pending'
            ]);

            foreach($cart as $item) {
                TransactionDetail::create([
                    'transaction_id' => $trx->id,
                    'product_unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'price_at_purchase' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price']
                ]);

                // Kurangi Stok (Booking Barang)
                $pu = ProductUnit::find($item['unit_id']);
                $pu->product()->decrement('stock_in_base_unit', $item['quantity'] * $pu->conversion_to_base);
            }

            DB::commit();
            session()->forget('cart'); // Kosongkan keranjang

            return redirect()->route('orders.index')->with('success', 'Pesanan berhasil dibuat! Mohon tunggu konfirmasi kasir.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal checkout: ' . $e->getMessage());
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
        $order = Transaction::where('buyer_id', Auth::id())->with('details.productUnit.product')->findOrFail($id);
        return view('customer.order.show', compact('order'));
    }
}