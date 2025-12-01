@extends('layouts.customer')

@section('content')
<div class="min-h-screen bg-gray-50 pb-32">
    <!-- Header -->
    <div class="bg-white p-6 border-b border-gray-200 shadow-sm sticky top-0 z-40">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-black text-gray-900">Keranjang Belanja</h1>
                    <p class="text-sm text-gray-500">{{ count(session('cart', [])) }} item</p>
                </div>
            </div>
            @if(session('cart') && count(session('cart')) > 0)
            <form action="{{ route('cart.clear') }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Hapus semua item dari keranjang?')" 
                        class="text-red-500 hover:text-red-700 text-sm font-bold">
                    Hapus Semua
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('cart') && count(session('cart')) > 0)
        <!-- List Items -->
        <div class="p-4 space-y-3">
            @foreach(session('cart') as $key => $item)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <div class="flex gap-4">
                    <!-- Gambar -->
                    <div class="h-20 w-20 bg-gray-100 rounded-xl flex-shrink-0 overflow-hidden">
                        @if($item['image'])
                            <img src="{{ Storage::url($item['image']) }}" class="h-full w-full object-cover">
                        @else
                            <div class="h-full w-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900 text-sm leading-tight line-clamp-2">{{ $item['product_name'] }}</h3>
                                <p class="text-xs text-gray-500 mt-1">{{ $item['unit_name'] }}</p>
                            </div>
                            <form action="{{ route('cart.remove', $key) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>

                        <div class="flex items-end justify-between mt-4">
                            <div class="flex items-center gap-3">
                                <!-- Quantity Controls -->
                                <form action="{{ route('cart.update', $key) }}" method="POST" class="flex items-center gap-2">
                                    @csrf @method('PATCH')
                                    <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}" 
                                            {{ $item['quantity'] <= 1 ? 'disabled' : '' }}
                                            class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200 disabled:opacity-30">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                    
                                    <span class="text-sm font-bold text-gray-900 min-w-8 text-center">{{ $item['quantity'] }}</span>
                                    
                                    <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}"
                                            class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            
                            <div class="text-right">
                                <div class="text-lg font-black text-indigo-600">
                                    Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Rp {{ number_format($item['price'], 0, ',', '.') }}/item
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Total & Checkout -->
        <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-6 z-50 shadow-2xl">
            <div class="max-w-md mx-auto">
                <!-- Summary -->
                <div class="flex justify-between items-center mb-4">
                    <div class="text-sm text-gray-600">Total ({{ count(session('cart')) }} item)</div>
                    <div class="text-2xl font-black text-gray-900">Rp {{ number_format($total, 0, ',', '.') }}</div>
                </div>
                
                <!-- Checkout Button -->
                <a href="{{ route('checkout.form') }}" 
                   class="block w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center py-4 rounded-2xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all">
                    Lanjutkan Checkout
                </a>
                
                <!-- Continue Shopping -->
                <a href="{{ route('home') }}" class="block text-center text-gray-500 hover:text-gray-700 text-sm font-medium mt-3">
                    + Tambah Produk Lainnya
                </a>
            </div>
        </div>

    @else
        <!-- Empty State -->
        <div class="flex flex-col items-center justify-center py-20 px-4 text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Keranjang Kosong</h3>
            <p class="text-gray-500 mb-8 max-w-sm">Belum ada produk di keranjang belanja Anda. Yuk mulai belanja!</p>
            <a href="{{ route('home') }}" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-indigo-700 shadow-lg transition-all">
                Jelajahi Produk
            </a>
        </div>
    @endif
</div>
@endsection