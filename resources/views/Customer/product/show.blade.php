@extends('layouts.customer')

@section('content')
<div class="pb-20 bg-white min-h-screen">
    
    <!-- Gambar Produk -->
    <div class="relative bg-gray-100 h-80 w-full flex items-center justify-center overflow-hidden">
        @if($product->foto_produk)
            <img src="{{ Storage::url($product->foto_produk) }}" class="w-full h-full object-cover">
        @else
            <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.001M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        @endif
        <a href="{{ route('home') }}" class="absolute top-4 left-4 bg-white/80 p-2 rounded-full text-gray-800 hover:bg-white shadow">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
    </div>

    <!-- Info Produk -->
    <div class="p-5 -mt-6 bg-white rounded-t-3xl relative z-10">
        <div class="flex justify-between items-start">
            <div>
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider bg-indigo-50 px-2 py-1 rounded">{{ $product->category->name ?? 'Umum' }}</span>
                <h1 class="text-2xl font-black text-gray-900 mt-2 leading-tight">{{ $product->name }}</h1>
                <div class="text-sm text-gray-500 font-mono mt-1">Kode: {{ $product->kode_produk }}</div>
            </div>
        </div>

        <div class="mt-6" x-data="{ selectedUnit: {{ $product->baseUnit->id }}, price: {{ $product->baseUnit->price }} }">
            
            <!-- Pilihan Satuan -->
            <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Kemasan:</label>
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($product->units as $unit)
                <button @click="selectedUnit = {{ $unit->id }}; price = {{ $unit->price }}" 
                        :class="selectedUnit == {{ $unit->id }} ? 'ring-2 ring-indigo-600 bg-indigo-50 text-indigo-700' : 'bg-gray-50 border border-gray-200 text-gray-600'"
                        class="px-4 py-2 rounded-lg text-sm font-bold transition-all">
                    {{ $unit->unit->name }}
                </button>
                @endforeach
            </div>

            <!-- Harga Dinamis -->
            <div class="flex items-end gap-1 mb-6">
                <span class="text-sm font-bold text-gray-500 mb-1">Harga:</span>
                <span class="text-3xl font-black text-gray-900">Rp <span x-text="new Intl.NumberFormat('id-ID').format(price)"></span></span>
            </div>

            <!-- Deskripsi -->
            @if($product->description)
            <div class="border-t border-gray-100 pt-4">
                <h3 class="font-bold text-gray-800 mb-2">Deskripsi</h3>
                <p class="text-sm text-gray-600 leading-relaxed">{{ $product->description }}</p>
            </div>
            @endif

            <!-- Footer Beli -->
            <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-4 z-50 shadow-lg safe-area-pb">
                <div class="max-w-md mx-auto flex gap-4">
                    <form action="{{ route('cart.add') }}" method="POST" class="flex-1 flex gap-4">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="unit_id" x-model="selectedUnit">
                        
                        <div class="w-24 relative">
                            <button type="button" onclick="if(this.nextElementSibling.value > 1) this.nextElementSibling.value--" class="absolute left-0 top-0 h-full w-8 text-gray-600 hover:bg-gray-100 rounded-l-lg font-bold">-</button>
                            <input type="number" name="quantity" value="1" min="1" class="w-full text-center border-gray-300 rounded-lg h-12 font-bold text-lg focus:ring-indigo-500 focus:border-indigo-500" readonly>
                            <button type="button" onclick="this.previousElementSibling.value++" class="absolute right-0 top-0 h-full w-8 text-gray-600 hover:bg-gray-100 rounded-r-lg font-bold">+</button>
                        </div>
                        
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            + Keranjang
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection