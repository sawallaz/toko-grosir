@extends('layouts.customer')

@section('title', 'Beranda - FADLIMART')
@section('content')
<div class="min-h-screen pb-6">
    <!-- 1. HERO BANNER (Enhanced) -->
    @if(!request('search') && !request('category'))
    <div class="mb-8">
        <div class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 rounded-3xl p-6 md:p-12 text-white shadow-2xl relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 left-0 w-72 h-72 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full translate-x-1/3 translate-y-1/3"></div>
            </div>
            
            <div class="relative z-10 max-w-2xl">
                <span class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm text-white text-sm font-bold px-4 py-2 rounded-full uppercase tracking-wide mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    PROMO SPESIAL
                </span>
                <h1 class="text-4xl md:text-6xl font-black leading-tight mb-4">
                    Belanja Grosir<br>
                    <span class="bg-gradient-to-r from-yellow-300 to-orange-300 bg-clip-text text-transparent">Harga Pabrik!</span>
                </h1>
                <p class="text-indigo-100 text-lg md:text-xl mb-8 leading-relaxed max-w-xl">
                    Dapatkan harga termurah langsung dari pabrik untuk kebutuhan warung dan rumah tangga Anda. Gratis ongkir!
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="#produk-terbaru" class="bg-white text-indigo-700 px-8 py-4 rounded-2xl font-bold shadow-2xl hover:shadow-3xl hover:scale-105 transition-all duration-300 flex items-center justify-center gap-3 group">
                        <svg class="w-5 h-5 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Belanja Sekarang
                    </a>
                    <a href="#kategori" class="border-2 border-white/30 text-white px-8 py-4 rounded-2xl font-bold backdrop-blur-sm hover:bg-white/10 transition-all duration-300">
                        Lihat Kategori
                    </a>
                </div>
            </div>

            <!-- Floating Elements -->
            <div class="absolute top-8 right-8 opacity-20 animate-float">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                </svg>
            </div>
        </div>
    </div>
    @endif

    <!-- 2. KATEGORI SECTION (Enhanced) -->
    <div id="kategori" class="mb-12">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-black text-gray-900 mb-2">Kategori Pilihan</h2>
                <p class="text-gray-500">Temukan produk berdasarkan kategori</p>
            </div>
            @if(request('category'))
                <a href="{{ route('home') }}" class="text-sm text-red-500 font-bold hover:text-red-600 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Reset Filter
                </a>
            @endif
        </div>
        
        <!-- Categories Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
            <!-- All Categories -->
            <a href="{{ route('home') }}" 
               class="group bg-white rounded-2xl p-4 shadow-sm border-2 transition-all duration-300 hover:shadow-xl flex flex-col items-center text-center {{ !request('category') ? 'border-indigo-600 shadow-md' : 'border-gray-200 hover:border-indigo-400' }}">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                </div>
                <span class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Semua</span>
            </a>

            @foreach($categories as $cat)
            <a href="{{ route('home', ['category' => $cat->id]) }}" 
               class="group bg-white rounded-2xl p-4 shadow-sm border-2 transition-all duration-300 hover:shadow-xl flex flex-col items-center text-center {{ request('category') == $cat->id ? 'border-indigo-600 shadow-md' : 'border-gray-200 hover:border-indigo-400' }}">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                </div>
                <span class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors line-clamp-2">{{ $cat->name }}</span>
            </a>
            @endforeach
        </div>
    </div>

    <!-- 3. PRODUCTS GRID (Enhanced) -->
    <div id="produk-terbaru">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-black text-gray-900 mb-2">Produk Terbaru</h2>
                <p class="text-gray-500">Temukan produk terbaik dengan harga grosir</p>
            </div>
            
            <!-- Sort Options -->
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-500 hidden md:block">Urutkan:</span>
                <select class="text-sm border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-indigo-500">
                    <option>Terbaru</option>
                    <option>Harga Terendah</option>
                    <option>Harga Tertinggi</option>
                    <option>Stok Terbanyak</option>
                </select>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
            @forelse($products as $product)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-2xl hover:border-indigo-200 transition-all duration-300 group flex flex-col h-full">
                <!-- Product Image -->
                <div class="aspect-square w-full bg-gradient-to-br from-gray-50 to-gray-100 relative overflow-hidden">
                    @if($product->foto_produk)
                        <img 
                            src="{{ Storage::url($product->foto_produk) }}" 
                            alt="{{ $product->name }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            loading="lazy"
                        >
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.001M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                    
                    <!-- Stock Badge -->
                    @if($product->stock_in_base_unit == 0)
                        <div class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center">
                            <span class="bg-red-600 text-white text-xs font-bold px-3 py-2 rounded-full shadow-lg">STOK HABIS</span>
                        </div>
                    @elseif($product->stock_in_base_unit < 10)
                        <div class="absolute top-3 left-3">
                            <span class="bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow">STOK TERBATAS</span>
                        </div>
                    @endif

                    <!-- Category Badge -->
                    <div class="absolute top-3 right-3">
                        <span class="bg-black/70 text-white text-xs font-bold px-2 py-1 rounded-full backdrop-blur-sm">
                            {{ $product->category->name ?? 'Umum' }}
                        </span>
                    </div>

                    <!-- Quick Actions Overlay -->
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <a href="{{ route('product.show', $product->id) }}" 
                           class="bg-white text-gray-900 px-4 py-2 rounded-xl font-bold hover:bg-gray-100 transition-all transform translate-y-4 group-hover:translate-y-0 duration-300 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Lihat Detail
                        </a>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="p-4 flex-1 flex flex-col">
                    <h4 class="font-bold text-gray-900 text-sm leading-snug mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors">
                        {{ $product->name }}
                    </h4>
                    
                    <div class="mt-auto space-y-3">
                        <!-- Price -->
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs text-gray-500 font-medium">Rp</span>
                            <span class="text-lg font-black text-gray-900">
                                {{ number_format($product->baseUnit->price ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        
                        <!-- Unit & Stock -->
                        <div class="flex justify-between items-center text-xs text-gray-500">
                            <span>per {{ $product->baseUnit->unit->name ?? 'Unit' }}</span>
                            @if($product->stock_in_base_unit > 0)
                                <span class="text-green-600 font-medium">
                                    Stok: {{ floor($product->stock_in_base_unit / ($product->baseUnit->conversion_to_base ?? 1)) }}
                                </span>
                            @endif
                        </div>

                        <!-- Add to Cart Button -->
                        @if($product->stock_in_base_unit > 0)
                        <form action="{{ route('cart.add') }}" method="POST" class="mt-2">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="unit_id" value="{{ $product->baseUnit->id }}">
                            <input type="hidden" name="quantity" value="1">
                            
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-3 rounded-xl hover:shadow-lg transform hover:scale-105 transition-all duration-300 flex items-center justify-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Tambah Keranjang
                            </button>
                        </form>
                        @else
                        <button disabled 
                                class="w-full bg-gray-200 text-gray-500 font-bold py-3 rounded-xl cursor-not-allowed text-sm">
                            Stok Habis
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <!-- Empty State -->
            <div class="col-span-full text-center py-16">
                <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-black text-gray-900 mb-3">Tidak Ada Produk Ditemukan</h3>
                <p class="text-gray-500 mb-8 max-w-sm mx-auto">
                    @if(request('search'))
                        Tidak ada hasil untuk "{{ request('search') }}". Coba kata kunci lain.
                    @elseif(request('category'))
                        Tidak ada produk dalam kategori ini.
                    @else
                        Belum ada produk yang tersedia.
                    @endif
                </p>
                <a href="{{ route('home') }}" 
                   class="inline-flex items-center gap-2 bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-indigo-700 shadow-lg transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    Lihat Semua Produk
                </a>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
        <div class="mt-12 flex justify-center">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
                {{ $products->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(12deg); }
        50% { transform: translateY(-10px) rotate(12deg); }
    }
    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
</style>

@endsection