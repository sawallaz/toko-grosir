@extends('layouts.customer')

@section('title', 'Beranda - FADLIMART')
@section('content')
<div class="min-h-screen pb-6 fade-in">
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

    <!-- KATEGORI SECTION -->
    <div id="kategori" class="mb-12 animate-slide-up">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-black text-gray-900 mb-2">Kategori Produk</h2>
                <p class="text-gray-500">Pilih berdasarkan kategori favorit Anda</p>
            </div>
            @if(request('category'))
                <a href="{{ route('home') }}" class="text-sm text-red-500 font-bold hover:text-red-600 transition-colors flex items-center gap-2">
                    ✕ Reset Filter
                </a>
            @endif
        </div>
        
        <!-- Categories -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <!-- All -->
            <a href="{{ route('home') }}" 
               class="group bg-white p-4 rounded-2xl shadow-soft border-2 transition-all-300 hover:shadow-hard flex flex-col items-center text-center {{ !request('category') ? 'border-indigo-600 shadow-md' : 'border-gray-200 hover:border-indigo-400' }}">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                </div>
                <span class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">Semua</span>
            </a>

            @foreach($categories as $cat)
            <a href="{{ route('home', ['category' => $cat->id]) }}" 
               class="group bg-white p-4 rounded-2xl shadow-soft border-2 transition-all-300 hover:shadow-hard flex flex-col items-center text-center {{ request('category') == $cat->id ? 'border-indigo-600 shadow-md' : 'border-gray-200 hover:border-indigo-400' }}">
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

    <!-- PRODUCTS SECTION -->
    <div id="produk" class="animate-slide-up">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-black text-gray-900 mb-2">Produk Terbaru</h2>
                <p class="text-gray-500">Temukan produk terbaik dengan harga grosir</p>
            </div>
            
            <!-- Filter & Sort -->
            <div class="flex items-center gap-4">
                <select id="sortProducts" class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="newest">Terbaru</option>
                    <option value="price_low">Harga: Rendah ke Tinggi</option>
                    <option value="price_high">Harga: Tinggi ke Rendah</option>
                    <option value="stock">Stok Terbanyak</option>
                </select>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div id="products-container">
            @include('customer.partials.products-grid', ['products' => $products])
        </div>

        <!-- Load More Button (AJAX) -->
        @if($products->hasMorePages())
        <div class="mt-8 text-center">
            <button id="load-more" 
                    data-page="2" 
                    data-category="{{ request('category') }}" 
                    data-search="{{ request('search') }}"
                    class="bg-white border-2 border-indigo-600 text-indigo-600 px-8 py-3 rounded-xl font-bold hover:bg-indigo-50 transition-all">
                Muat Lebih Banyak
            </button>
        </div>
        @endif
    </div>
</div>

<!-- Quick View Modal -->
<div id="quick-view-modal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-auto animate-slide-up">
        <!-- Modal content will be loaded via AJAX -->
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sort products
    const sortSelect = document.getElementById('sortProducts');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('sort', this.value);
            window.app.loadPage(url.toString());
        });
    }

    // Load more products
    const loadMoreBtn = document.getElementById('load-more');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', async function() {
            const page = this.dataset.page;
            const category = this.dataset.category;
            const search = this.dataset.search;
            
            this.disabled = true;
            this.innerHTML = '<span class="animate-spin">⟳</span> Memuat...';
            
            try {
                const response = await fetch(`?page=${page}&category=${category}&search=${search}&ajax=1`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const html = await response.text();
                
                // Append new products
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const newProducts = tempDiv.querySelector('#products-container');
                
                if (newProducts) {
                    document.getElementById('products-container').insertAdjacentHTML('beforeend', newProducts.innerHTML);
                    
                    // Update page number
                    this.dataset.page = parseInt(page) + 1;
                    
                    // Check if there are more pages
                    if (!tempDiv.querySelector('#load-more')) {
                        this.remove();
                    }
                }
                
                this.disabled = false;
                this.innerHTML = 'Muat Lebih Banyak';
                
            } catch (error) {
                console.error('Error loading more products:', error);
                this.innerHTML = 'Error, coba lagi';
                setTimeout(() => {
                    this.disabled = false;
                    this.innerHTML = 'Muat Lebih Banyak';
                }, 2000);
            }
        });
    }

    // Quick view functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-quick-view]')) {
            const productId = e.target.closest('[data-quick-view]').dataset.productId;
            showQuickView(productId);
        }
        
        // Close modal
        if (e.target.id === 'quick-view-modal' || e.target.closest('[data-close-modal]')) {
            hideQuickView();
        }
    });
    
    async function showQuickView(productId) {
        const modal = document.getElementById('quick-view-modal');
        const modalContent = modal.querySelector('.bg-white');
        
        try {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            modalContent.innerHTML = `
                <div class="p-8">
                    <div class="flex justify-between items-start mb-6">
                        <h3 class="text-xl font-bold">Memuat...</h3>
                        <button data-close-modal class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="h-64 skeleton rounded-xl"></div>
                </div>
            `;
            
            const response = await fetch(`/product/${productId}/quick-view`);
            const html = await response.text();
            
            modalContent.innerHTML = html;
            
        } catch (error) {
            modalContent.innerHTML = `
                <div class="p-8">
                    <div class="text-center text-red-500">
                        Gagal memuat detail produk
                    </div>
                </div>
            `;
        }
    }
    
    function hideQuickView() {
        const modal = document.getElementById('quick-view-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
});
</script>
@endsection