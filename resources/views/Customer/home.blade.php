@extends('layouts.customer')

@section('content')
<!-- 1. Pencarian Sticky -->
<div class="sticky top-14 z-40 bg-gray-50 px-4 py-2 border-b border-gray-200/50 backdrop-blur-md bg-opacity-90">
    <form action="{{ route('home') }}" method="GET">
        <div class="relative">
            <input type="search" id="search-input" name="search" value="{{ request('search') }}" 
                   class="w-full bg-white border border-gray-200 rounded-xl py-3 pl-11 pr-4 text-sm font-medium shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" 
                   placeholder="Cari beras, minyak, gula...">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>
    </form>
</div>

<div class="p-4 space-y-6">

    <!-- 2. Kategori (Scroll Horizontal) -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider">Kategori</h3>
            @if(request('category'))
                <a href="{{ route('home') }}" class="text-xs text-red-500 font-bold hover:underline">Reset</a>
            @endif
        </div>
        <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 no-scrollbar">
            <a href="{{ route('home') }}" class="flex-shrink-0 px-4 py-2 rounded-full text-xs font-bold transition-all border {{ !request('category') ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                Semua
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('home', ['category' => $cat->id]) }}" class="flex-shrink-0 px-4 py-2 rounded-full text-xs font-bold transition-all border {{ request('category') == $cat->id ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- 3. Banner Promo (Opsional - Pemanis) -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-5 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10">
            <div class="text-xs font-bold text-indigo-200 uppercase mb-1">Selamat Datang</div>
            <h2 class="text-2xl font-black leading-tight">Belanja Grosir<br>Lebih Murah!</h2>
        </div>
        <div class="absolute right-[-20px] bottom-[-20px] opacity-20 rotate-12">
            <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path></svg>
        </div>
    </div>

    <!-- 4. Grid Produk -->
    <div>
        <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider mb-4">Produk Terbaru</h3>
        
        <div class="grid grid-cols-2 gap-4">
            @forelse($products as $product)
            <a href="{{ route('product.show', $product->id) }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group block">
                <!-- Gambar -->
                <div class="aspect-square w-full bg-gray-100 relative overflow-hidden">
                    @if($product->foto_produk)
                        <img src="{{ Storage::url($product->foto_produk) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.001M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    @endif
                    
                    @if($product->stock_in_base_unit == 0)
                        <div class="absolute inset-0 bg-white/50 backdrop-blur-sm flex items-center justify-center">
                            <span class="bg-red-600 text-white text-xs font-bold px-2 py-1 rounded shadow">HABIS</span>
                        </div>
                    @endif
                </div>

                <!-- Info -->
                <div class="p-3">
                    <div class="text-[10px] text-gray-400 uppercase font-bold mb-1 tracking-wide truncate">{{ $product->category->name ?? 'Umum' }}</div>
                    <h4 class="font-bold text-gray-800 text-sm leading-snug mb-2 line-clamp-2 h-10">{{ $product->name }}</h4>
                    
                    <div class="flex flex-col">
                        <span class="text-[10px] text-gray-500">Mulai dari</span>
                        <div class="text-indigo-600 font-black text-lg leading-none">
                            Rp {{ number_format($product->baseUnit->price ?? 0, 0, ',', '.') }}
                        </div>
                        <div class="text-[10px] text-gray-400 mt-0.5">
                            / {{ $product->baseUnit->unit->name ?? 'Unit' }}
                        </div>
                    </div>
                </div>
                
                <!-- Tombol Add (Visual Only, klik card utk detail) -->
                <div class="px-3 pb-3">
                    <div class="w-full bg-gray-50 text-indigo-600 text-xs font-bold py-2 rounded-lg text-center group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        LIHAT DETAIL
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-2 text-center py-12 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <p class="text-sm">Produk tidak ditemukan.</p>
            </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    </div>

</div>
@endsection