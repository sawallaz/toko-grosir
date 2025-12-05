@extends('layouts.customer')

@section('content')

    <!-- 1. BANNER PROMO (Hanya tampil jika tidak sedang cari) -->
    @if(!request('search') && !request('category'))
    <div class="mb-8">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 md:p-10 text-white shadow-xl relative overflow-hidden">
            <div class="relative z-10 max-w-lg">
                <span class="bg-white/20 text-white text-xs font-bold px-2 py-1 rounded backdrop-blur-sm uppercase tracking-wide">Promo Spesial</span>
                <h2 class="text-3xl md:text-4xl font-black leading-tight mt-2 mb-4">Belanja Grosir<br>Harga Pabrik!</h2>
                <p class="text-indigo-100 text-sm md:text-base mb-6">Dapatkan harga termurah untuk kebutuhan warung dan rumah tangga Anda.</p>
                <a href="#produk-terbaru" class="bg-white text-indigo-700 px-6 py-3 rounded-xl font-bold shadow-lg hover:bg-gray-100 transition inline-block">Belanja Sekarang</a>
            </div>
            <!-- Dekorasi -->
            <div class="absolute -right-10 -bottom-20 opacity-20 rotate-12">
                <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 20 20"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path></svg>
            </div>
        </div>
    </div>
    @endif

    <!-- 2. KATEGORI (Scroll Horizontal) -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-black text-gray-800">Kategori Pilihan</h3>
            @if(request('category'))
                <a href="{{ route('home') }}" class="text-sm text-red-500 font-bold hover:underline">Reset Filter</a>
            @endif
        </div>
        
        <div class="flex gap-3 overflow-x-auto pb-4 no-scrollbar">
            <a href="{{ route('home') }}" class="flex-shrink-0 px-5 py-2.5 rounded-full text-sm font-bold transition-all border {{ !request('category') ? 'bg-gray-900 text-white border-gray-900 shadow-lg' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                Semua
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('home', ['category' => $cat->id]) }}" class="flex-shrink-0 px-5 py-2.5 rounded-full text-sm font-bold transition-all border {{ request('category') == $cat->id ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- 3. GRID PRODUK (RESPONSIVE) -->
    <div id="produk-terbaru">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-black text-gray-800">Produk Terbaru</h3>
        </div>
        
        <!-- Grid Responsif: HP(2), Tablet(3), Laptop(4/5) -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
            @forelse($products as $product)
            <a href="{{ route('product.show', $product->id) }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:border-indigo-200 transition-all group flex flex-col h-full">
                
                <!-- Gambar -->
                <div class="aspect-square w-full bg-gray-50 relative overflow-hidden">
                    @if($product->foto_produk)
                        <img src="{{ Storage::url($product->foto_produk) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.001M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    @endif
                    
                    <!-- Badge Stok -->
                    @if($product->stock_in_base_unit == 0)
                        <div class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center">
                            <span class="bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow">STOK HABIS</span>
                        </div>
                    @endif
                </div>

                <!-- Info Produk -->
                <div class="p-4 flex-1 flex flex-col">
                    <div class="text-[10px] text-gray-400 uppercase font-bold mb-1 tracking-wider truncate">{{ $product->category->name ?? 'Umum' }}</div>
                    
                    <h4 class="font-bold text-gray-800 text-sm md:text-base leading-snug mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors">{{ $product->name }}</h4>
                    
                    <div class="mt-auto">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs text-gray-500 font-medium">Rp</span>
                            <span class="text-lg md:text-xl font-black text-gray-900">{{ number_format($product->baseUnit->price ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-[10px] text-gray-400 mb-3">
                            per {{ $product->baseUnit->unit->name ?? 'Unit' }}
                        </div>
                        
                        <!-- Tombol Beli -->
                        <button class="w-full bg-gray-50 text-indigo-600 font-bold py-2.5 rounded-lg text-xs md:text-sm hover:bg-indigo-600 hover:text-white transition-all border border-indigo-100 group-hover:border-indigo-600 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            BELI
                        </button>
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-full text-center py-20">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <h3 class="text-lg font-bold text-gray-600">Tidak ada produk ditemukan</h3>
                <p class="text-gray-400 text-sm">Coba kata kunci lain atau reset filter.</p>
                <a href="{{ route('home') }}" class="inline-block mt-4 px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold text-sm hover:bg-indigo-700">Lihat Semua Produk</a>
            </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    </div>

</div>
@endsection