@extends('layouts.customer')

@section('content')
<div class="p-4 pb-24">
    <h1 class="text-xl font-black text-gray-800 mb-4 flex items-center gap-2">
        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        Keranjang Belanja
    </h1>

    @if(session('cart') && count(session('cart')) > 0)
        <div class="space-y-4">
            @foreach(session('cart') as $key => $item)
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex gap-4 relative">
                <!-- Hapus -->
                <form action="{{ route('cart.remove', $key) }}" method="POST" class="absolute top-2 right-2">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-gray-300 hover:text-red-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </form>

                <!-- Gambar -->
                <div class="h-20 w-20 bg-gray-100 rounded-lg flex-shrink-0 overflow-hidden">
                    @if($item['image']) <img src="{{ Storage::url($item['image']) }}" class="h-full w-full object-cover">
                    @else <div class="h-full w-full flex items-center justify-center text-xs text-gray-400">IMG</div> @endif
                </div>

                <!-- Info -->
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800 line-clamp-1">{{ $item['product_name'] }}</h3>
                    <div class="text-xs text-gray-500 mb-2">{{ $item['unit_name'] }}</div>
                    <div class="flex justify-between items-end">
                        <div class="text-indigo-600 font-bold">Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                        <div class="text-sm font-bold bg-gray-100 px-2 py-1 rounded text-gray-700">x {{ $item['quantity'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Footer Checkout -->
        <div class="fixed bottom-16 left-0 w-full bg-white border-t border-gray-200 p-4 z-40 shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
            <div class="max-w-md mx-auto flex justify-between items-center">
                <div>
                    <div class="text-xs text-gray-500 font-bold uppercase">Total Bayar</div>
                    <div class="text-2xl font-black text-gray-900">Rp {{ number_format($total, 0, ',', '.') }}</div>
                </div>
                <form action="{{ route('checkout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg transform active:scale-95 transition">
                        Pesan Sekarang
                    </button>
                </form>
            </div>
        </div>

    @else
        <div class="text-center py-20 text-gray-400">
            <svg class="w-20 h-20 mx-auto mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <p>Keranjang masih kosong.</p>
            <a href="{{ route('home') }}" class="text-indigo-600 font-bold hover:underline mt-2 inline-block">Mulai Belanja</a>
        </div>
    @endif
</div>
@endsection