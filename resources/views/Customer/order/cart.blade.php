@extends('layouts.customer')

@section('content')
<div class="p-4 pb-32"> <!-- Extra padding bottom for footer -->
    <h1 class="text-xl font-black text-gray-800 mb-4 flex items-center gap-2">
        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        Keranjang Belanja
    </h1>

    @if(session('cart') && count(session('cart')) > 0)
        <div class="space-y-4">
            @foreach(session('cart') as $key => $item)
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex gap-4 relative">
                <form action="{{ route('cart.remove', $key) }}" method="POST" class="absolute top-2 right-2">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-gray-300 hover:text-red-500 p-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </form>
                <div class="h-16 w-16 bg-gray-100 rounded-lg flex-shrink-0 overflow-hidden">
                    @if($item['image']) <img src="{{ Storage::url($item['image']) }}" class="h-full w-full object-cover"> @else <div class="h-full w-full flex items-center justify-center text-xs text-gray-400">IMG</div> @endif
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800 line-clamp-1">{{ $item['product_name'] }}</h3>
                    <div class="text-xs text-gray-500 mb-1">{{ $item['unit_name'] }}</div>
                    <div class="flex justify-between items-center">
                        <div class="text-indigo-600 font-bold">Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                        <div class="flex items-center border border-gray-200 rounded">
                            <!-- Tombol Update Qty bisa ditambahkan disini jika mau -->
                            <span class="px-3 py-1 text-sm font-bold bg-gray-50 text-gray-700">{{ $item['quantity'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- FORM CHECKOUT -->
        <form action="{{ route('checkout') }}" method="POST">
            @csrf
            <div class="fixed bottom-16 left-0 w-full bg-white border-t border-gray-200 p-4 z-40 shadow-[0_-4px_20px_rgba(0,0,0,0.05)] rounded-t-2xl">
                <div class="max-w-md mx-auto space-y-4">
                    
                    <!-- Pilihan Pembayaran -->
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="online" class="peer sr-only" required>
                            <div class="border-2 border-gray-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 rounded-xl p-3 text-center transition-all">
                                <div class="font-bold text-sm text-gray-700 peer-checked:text-indigo-700">Bayar Online</div>
                                <div class="text-[10px] text-gray-500">QRIS / Transfer</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="store" class="peer sr-only" required>
                            <div class="border-2 border-gray-200 peer-checked:border-green-600 peer-checked:bg-green-50 rounded-xl p-3 text-center transition-all">
                                <div class="font-bold text-sm text-gray-700 peer-checked:text-green-700">Ambil di Toko</div>
                                <div class="text-[10px] text-gray-500">Bayar Tunai</div>
                            </div>
                        </label>
                    </div>

                    <div class="flex justify-between items-center border-t pt-3">
                        <div>
                            <div class="text-xs text-gray-500 font-bold uppercase">Total Bayar</div>
                            <div class="text-2xl font-black text-gray-900">Rp {{ number_format($total, 0, ',', '.') }}</div>
                        </div>
                        <button type="submit" class="bg-gray-900 hover:bg-black text-white px-6 py-3 rounded-xl font-bold shadow-lg transform active:scale-95 transition">
                            Checkout
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="text-center py-20 text-gray-400">
            <p>Keranjang kosong.</p>
            <a href="{{ route('home') }}" class="text-indigo-600 font-bold hover:underline mt-2 inline-block">Belanja Sekarang</a>
        </div>
    @endif
</div>
@endsection