@extends('layouts.customer')

@section('content')
<div class="pb-24 bg-white min-h-screen">
    <!-- Gambar -->
    <div class="relative bg-gray-100 h-80 w-full flex items-center justify-center overflow-hidden">
        @if($product->foto_produk) <img src="{{ Storage::url($product->foto_produk) }}" class="w-full h-full object-cover"> @else <span class="text-gray-400">No Image</span> @endif
        <a href="{{ route('home') }}" class="absolute top-4 left-4 bg-white/80 p-2 rounded-full text-gray-800 hover:bg-white shadow"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></a>
    </div>

    <div class="p-5 -mt-6 bg-white rounded-t-3xl relative z-10 shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
        <span class="text-xs font-bold text-indigo-600 uppercase bg-indigo-50 px-2 py-1 rounded">{{ $product->category->name ?? 'Umum' }}</span>
        <h1 class="text-2xl font-black text-gray-900 mt-2 leading-tight">{{ $product->name }}</h1>
        <div class="text-sm text-gray-500 font-mono mt-1">Kode: {{ $product->kode_produk }}</div>

        <!-- Interactive Alpine Data -->
        <div class="mt-6" x-data="{ 
            units: {{ $product->units->map(fn($u)=>['id'=>$u->id, 'name'=>$u->unit->name, 'price'=>$u->price])->toJson() }},
            selectedUnitId: {{ $product->baseUnit->id }},
            qty: 1,
            get price() { return this.units.find(u => u.id == this.selectedUnitId).price }
        }">
            
            <!-- Pilih Satuan -->
            <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Kemasan:</label>
            <div class="flex flex-wrap gap-2 mb-4">
                <template x-for="u in units" :key="u.id">
                    <button @click="selectedUnitId = u.id" 
                            :class="selectedUnitId == u.id ? 'bg-indigo-600 text-white ring-2 ring-indigo-300' : 'bg-gray-100 text-gray-600 border-gray-200'"
                            class="px-4 py-2 rounded-lg text-sm font-bold transition-all border"
                            x-text="u.name">
                    </button>
                </template>
            </div>

            <!-- Harga -->
            <div class="flex items-end gap-1 mb-6">
                <span class="text-sm font-bold text-gray-500 mb-1">Harga:</span>
                <span class="text-3xl font-black text-gray-900">Rp <span x-text="new Intl.NumberFormat('id-ID').format(price)"></span></span>
            </div>

            <!-- Deskripsi -->
            @if($product->description)
            <div class="border-t border-gray-100 pt-4 mb-20">
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
                        <input type="hidden" name="unit_id" x-model="selectedUnitId">
                        
                        <!-- Qty Control -->
                        <div class="w-28 relative flex items-center">
                            <button type="button" @click="if(qty > 1) qty--" class="bg-gray-100 h-12 w-8 rounded-l-xl text-gray-600 font-bold hover:bg-gray-200">-</button>
                            <input type="number" name="quantity" x-model="qty" class="w-full text-center border-y border-x-0 border-gray-200 h-12 font-bold text-lg focus:ring-0 z-0" readonly>
                            <button type="button" @click="qty++" class="bg-gray-100 h-12 w-8 rounded-r-xl text-gray-600 font-bold hover:bg-gray-200">+</button>
                        </div>
                        
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">
                            <span>+ KERANJANG</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection