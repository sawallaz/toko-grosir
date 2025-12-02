<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'FADLIMART'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #fbbf24;
        }
        
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Animations */
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .animate-slide-up {
            animation: slideUp 0.3s ease-out;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Glass effect */
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        /* Custom utilities */
        .text-shadow { text-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .shadow-soft { box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); }
        .shadow-hard { box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15); }
        
        /* Smooth transitions */
        .transition-all-300 { transition: all 0.3s ease; }
        .transition-all-500 { transition: all 0.5s ease; }
        
        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        /* Floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-float { animation: float 3s ease-in-out infinite; }
    </style>
    
    <meta name="theme-color" content="#4f46e5">
    <meta name="description" content="Toko grosir online dengan harga termurah dan kualitas terbaik">
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased min-h-screen flex flex-col">

    <!-- HEADER -->
    <header class="bg-white shadow-sm sticky top-0 z-50 border-b border-gray-100">
        <div class="container mx-auto px-4 h-16 flex items-center justify-between gap-4">
            
            <!-- LOGO -->
            <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center gap-2 group">
                <div class="relative">
                    <svg class="w-8 h-8 text-indigo-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <div class="font-black text-xl tracking-tighter leading-none hidden sm:block">
                    <span class="text-indigo-600 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">FADLI</span>
                    <span class="text-gray-900">MART</span>
                </div>
            </a>

            <!-- SEARCH BAR -->
            <div class="flex-1 max-w-2xl mx-4">
                <form action="{{ route('home') }}" method="GET" class="relative group">
                    <input 
                        type="search" 
                        name="search" 
                        value="{{ request('search') }}" 
                        class="w-full bg-gray-100 border-2 border-transparent focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 rounded-xl py-2.5 pl-12 pr-4 text-sm transition-all duration-200 focus:shadow-lg" 
                        placeholder="Cari produk grosir murah..."
                        aria-label="Cari produk"
                    >
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    @if(request('search'))
                    <a href="{{ route('home') }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors" aria-label="Clear search">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                    @endif
                </form>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex items-center gap-2 md:gap-4 flex-shrink-0">
                
                <!-- Cart Button -->
                <a href="{{ route('cart.index') }}" class="relative p-2 rounded-xl hover:bg-gray-50 transition-colors group" aria-label="Keranjang belanja">
                    <svg class="w-6 h-6 text-gray-600 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    @auth
                        @php 
                            $cartCount = collect(session('cart', []))->sum('quantity') ?? 0;
                        @endphp
                        @if($cartCount > 0)
                            <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-bold h-5 w-5 flex items-center justify-center rounded-full border-2 border-white shadow-sm transform group-hover:scale-110 transition-transform">
                                {{ $cartCount > 99 ? '99+' : $cartCount }}
                            </span>
                        @endif
                    @endauth
                </a>

                <!-- Desktop Separator -->
                <div class="h-6 w-px bg-gray-300 hidden md:block"></div>

                <!-- Auth Section -->
                <div class="flex-shrink-0">
                    @auth
                    <!-- User Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button 
                            @click="open = !open" 
                            class="flex items-center gap-2 hover:bg-gray-50 rounded-xl p-2 transition-all"
                            :class="open ? 'bg-gray-50' : ''"
                            aria-label="Menu pengguna"
                        >
                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold border-2 border-white shadow-sm">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden lg:block text-left">
                                <div class="text-xs text-gray-500 font-medium">Halo,</div>
                                <div class="text-sm font-bold text-gray-800 leading-none truncate max-w-[120px]">{{ Auth::user()->name }}</div>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50 backdrop-blur-sm bg-white/95"
                             style="display: none;">
                            
                            <div class="px-4 py-3 border-b border-gray-100">
                                <div class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500 capitalize mt-1 flex items-center gap-1">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    {{ Auth::user()->role }}
                                </div>
                            </div>

                            <div class="py-1">
                                <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors group">
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Pesanan Saya
                                </a>

                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors group">
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Pengaturan Akun
                                </a>
                            </div>

                            <div class="border-t border-gray-100 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors group">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- Guest State -->
                    <div class="flex gap-2">
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-bold text-indigo-600 border border-indigo-600 rounded-xl hover:bg-indigo-50 transition-all">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all shadow-sm hover:shadow hidden md:block">
                            Daftar
                        </a>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex-1 container mx-auto px-4 py-6" id="main-content">
        <!-- Flash Messages -->
        @if(session('success') || session('error') || session('warning'))
        <div class="mb-6 space-y-3">
            @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-medium flex items-center gap-3 animate-slide-up">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('success') }}
            </div>
            @endif
            
            @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium flex items-center gap-3 animate-slide-up">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('error') }}
            </div>
            @endif
        </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </main>

    <!-- BOTTOM NAVIGATION -->
    <nav class="md:hidden fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 z-50 pb-safe shadow-[0_-4px_20px_rgba(0,0,0,0.08)] backdrop-blur-sm bg-white/95">
        <div class="grid grid-cols-4 h-16">
            <!-- Home -->
            <a href="{{ route('home') }}" class="flex flex-col items-center justify-center transition-colors group relative 
                {{ request()->routeIs('home') ? 'text-indigo-600' : 'text-gray-400 hover:text-indigo-600' }}">
                <div class="relative p-2 rounded-xl group-hover:bg-indigo-50 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <span class="text-[10px] font-bold mt-1">Beranda</span>
                @if(request()->routeIs('home'))
                <div class="absolute top-0 w-1 h-1 bg-indigo-600 rounded-full"></div>
                @endif
            </a>

            <!-- Pesanan -->
            <a href="{{ route('orders.index') }}" class="flex flex-col items-center justify-center transition-colors group relative 
                {{ request()->routeIs('orders.*') ? 'text-indigo-600' : 'text-gray-400 hover:text-indigo-600' }}">
                <div class="relative p-2 rounded-xl group-hover:bg-indigo-50 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <span class="text-[10px] font-bold mt-1">Pesanan</span>
            </a>

            <!-- Cart -->
            <a href="{{ route('cart.index') }}" class="flex flex-col items-center justify-center transition-colors group relative 
                {{ request()->routeIs('cart.*') ? 'text-indigo-600' : 'text-gray-400 hover:text-indigo-600' }}">
                <div class="relative p-2 rounded-xl group-hover:bg-indigo-50 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    @auth
                        @if($cartCount > 0)
                        <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full border-2 border-white transform group-hover:scale-110 transition-transform">
                            {{ $cartCount > 9 ? '9+' : $cartCount }}
                        </span>
                        @endif
                    @endauth
                </div>
                <span class="text-[10px] font-bold mt-1">Keranjang</span>
            </a>

            <!-- Account -->
            <a href="{{ Auth::check() ? route('profile.edit') : route('login') }}" class="flex flex-col items-center justify-center transition-colors group relative 
                {{ request()->routeIs('profile.*') ? 'text-indigo-600' : 'text-gray-400 hover:text-indigo-600' }}">
                <div class="relative p-2 rounded-xl group-hover:bg-indigo-50 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <span class="text-[10px] font-bold mt-1">Akun</span>
            </a>
        </div>
    </nav>

    <!-- AJAX Loader -->
    <div id="ajax-loader" class="fixed inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-[100] transition-opacity duration-300 opacity-0 pointer-events-none">
        <div class="text-center">
            <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600 font-medium">Memuat...</p>
        </div>
    </div>

   <script>
class FadliMartApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupAjax();
        this.setupLoading();
        this.setupCartActions();
        this.setupQuickActions();
        this.setupEventListeners();
    }

    // ====================
    // 1. SETUP LOADING
    // ====================
    setupLoading() {
        // Create global loading handler
        window.addEventListener('beforeunload', () => {
            this.showLoading();
        });
        
        // Handle form submissions
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.method === 'post' || form.method === 'POST') {
                this.showLoading();
            }
        });
        
        // Handle AJAX requests
        const originalFetch = window.fetch;
        window.fetch = async (...args) => {
            this.showLoading();
            try {
                return await originalFetch(...args);
            } finally {
                setTimeout(() => this.hideLoading(), 300);
            }
        };
    }

    // ====================
    // 2. LOADING METHODS
    // ====================
    showLoading() {
        const loader = document.getElementById('ajax-loader');
        if (loader) {
            loader.classList.remove('opacity-0', 'pointer-events-none');
            loader.classList.add('opacity-100');
        }
    }

    hideLoading() {
        const loader = document.getElementById('ajax-loader');
        if (loader) {
            loader.classList.remove('opacity-100');
            loader.classList.add('opacity-0', 'pointer-events-none');
        }
    }

    // ====================
    // 3. AJAX SETUP
    // ====================
    setupAjax() {
        // Intercept link clicks for AJAX navigation
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link || link.target === '_blank' || link.hasAttribute('download') || 
                link.getAttribute('href')?.startsWith('http') || 
                link.getAttribute('href')?.startsWith('#') ||
                link.getAttribute('href')?.startsWith('mailto') ||
                link.getAttribute('href')?.startsWith('tel')) {
                return;
            }

            const href = link.getAttribute('href');
            if (href && href !== 'javascript:void(0)') {
                e.preventDefault();
                this.loadPage(href);
            }
        });

        // Handle browser back/forward
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.url) {
                this.loadPage(e.state.url);
            }
        });
    }

    async loadPage(url) {
        this.showLoading();
        
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (!response.ok) throw new Error('Network response was not ok');

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update main content
            const mainContent = document.getElementById('main-content');
            const newContent = doc.querySelector('#main-content') || doc.body;
            
            // Smooth transition
            mainContent.style.opacity = '0';
            setTimeout(() => {
                mainContent.innerHTML = newContent.innerHTML;
                mainContent.style.opacity = '1';
                
                // Update URL without reload
                window.history.pushState({ url: url }, '', url);
                
                // Re-initialize components
                this.setupCartActions();
                this.setupQuickActions();
                this.setupEventListeners();
            }, 300);

        } catch (error) {
            console.error('Error loading page:', error);
            window.location.href = url; // Fallback to normal navigation
        } finally {
            setTimeout(() => this.hideLoading(), 500);
        }
    }

    // ====================
    // 4. CART ACTIONS
    // ====================
    setupCartActions() {
        console.log('Setting up cart actions...');
        
        // Handle add to cart forms
        document.addEventListener('submit', async (e) => {
            const form = e.target;
            
            // Check if this is a cart form
            const isCartForm = form.classList.contains('cart-form') || 
                              (form.action && form.action.includes('cart.add')) ||
                              form.querySelector('input[name="product_id"]');
            
            if (!isCartForm) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            try {
                this.showLoading();
                
                const formData = new FormData(form);
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn?.innerHTML;
                
                // Disable button and show loading
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="animate-spin">⟳</span> Menambahkan...';
                }
                
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                
                // Restore button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    
                    // Show success animation
                    if (data.success) {
                        submitBtn.innerHTML = '<span class="animate-pulse">✓</span> Berhasil!';
                        submitBtn.classList.remove('from-indigo-600', 'to-purple-600');
                        submitBtn.classList.add('bg-green-600');
                        
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.classList.remove('bg-green-600');
                            submitBtn.classList.add('from-indigo-600', 'to-purple-600');
                        }, 1500);
                    }
                }
                
                if (data.success) {
                    this.showCartSuccess(data.message, data.cartCount);
                    this.updateCartCount(data.cartCount);
                } else {
                    this.showToast(data.message || 'Gagal menambahkan ke keranjang', 'error');
                }
                
            } catch (error) {
                console.error('Cart Error:', error);
                this.showToast('Terjadi kesalahan jaringan', 'error');
            } finally {
                this.hideLoading();
            }
        });
    }

    // ====================
    // 5. QUICK ACTIONS
    // ====================
    setupQuickActions() {
        // Setup quick view modals
        document.addEventListener('click', (e) => {
            const quickViewBtn = e.target.closest('[data-quick-view]');
            if (quickViewBtn) {
                const productId = quickViewBtn.dataset.productId;
                this.showQuickView(productId);
            }
            
            // Close modal
            if (e.target.closest('[data-close-modal]') || e.target.id === 'quick-view-modal') {
                this.hideQuickView();
            }
        });
    }

    async showQuickView(productId) {
        // Implement quick view modal
        console.log('Quick view for product:', productId);
        // You'll implement this later
    }

    hideQuickView() {
        const modal = document.getElementById('quick-view-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    // ====================
    // 6. EVENT LISTENERS
    // ====================
    setupEventListeners() {
        // Sort products
        const sortSelect = document.getElementById('sortProducts');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                const url = new URL(window.location);
                url.searchParams.set('sort', e.target.value);
                this.loadPage(url.toString());
            });
        }

        // Load more products
        const loadMoreBtn = document.getElementById('load-more');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', async () => {
                const page = loadMoreBtn.dataset.page;
                const category = loadMoreBtn.dataset.category;
                const search = loadMoreBtn.dataset.search;
                
                loadMoreBtn.disabled = true;
                loadMoreBtn.innerHTML = '<span class="animate-spin">⟳</span> Memuat...';
                
                try {
                    const response = await fetch(`?page=${page}&category=${category}&search=${search}&ajax=1`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    
                    const html = await response.text();
                    
                    // Append new products
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const newProducts = tempDiv.querySelector('#products-container');
                    
                    if (newProducts) {
                        document.getElementById('products-container').insertAdjacentHTML('beforeend', newProducts.innerHTML);
                        loadMoreBtn.dataset.page = parseInt(page) + 1;
                        
                        // Check if there are more pages
                        if (!tempDiv.querySelector('#load-more')) {
                            loadMoreBtn.remove();
                        }
                    }
                    
                    loadMoreBtn.disabled = false;
                    loadMoreBtn.innerHTML = 'Muat Lebih Banyak';
                    
                } catch (error) {
                    console.error('Error loading more products:', error);
                    loadMoreBtn.innerHTML = 'Error, coba lagi';
                    setTimeout(() => {
                        loadMoreBtn.disabled = false;
                        loadMoreBtn.innerHTML = 'Muat Lebih Banyak';
                    }, 2000);
                }
            });
        }
    }

    // ====================
    // 7. NOTIFICATIONS
    // ====================
    showCartSuccess(message, count) {
        // Remove existing toasts
        document.querySelectorAll('.cart-success-toast').forEach(toast => toast.remove());
        
        const toast = document.createElement('div');
        toast.className = 'cart-success-toast fixed top-6 right-6 p-4 bg-white rounded-2xl shadow-2xl animate-slide-up z-[9999] border border-gray-200 max-w-sm';
        toast.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-gray-900 text-sm">${message}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        Keranjang: <span class="font-bold text-indigo-600">${count} item</span>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <a href="{{ route('cart.index') }}" 
                           class="flex-1 bg-indigo-600 text-white text-center text-xs font-bold py-2 rounded-lg hover:bg-indigo-700 transition-all">
                            Lihat Keranjang
                        </a>
                        <button onclick="this.closest('.cart-success-toast').remove()" 
                                class="flex-1 bg-gray-100 text-gray-700 text-center text-xs font-bold py-2 rounded-lg hover:bg-gray-200 transition-all">
                            Tutup
                        </button>
                    </div>
                </div>
                <button onclick="this.closest('.cart-success-toast').remove()" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 4000);
    }

    showToast(message, type = 'info') {
        // Remove existing toasts
        document.querySelectorAll('.app-toast').forEach(toast => toast.remove());
        
        const toast = document.createElement('div');
        toast.className = `app-toast fixed top-6 right-6 p-4 rounded-xl shadow-hard animate-slide-up z-[100] ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        toast.innerHTML = `
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${type === 'success' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>' :
                    type === 'error' ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>' :
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'}
                </svg>
                <span class="font-medium">${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }

    // ====================
    // 8. CART UI UPDATES
    // ====================
    updateCartCount(count) {
        // Update cart count in header
        const cartBadges = document.querySelectorAll('[data-cart-count], .cart-badge');
        cartBadges.forEach(badge => {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.toggle('hidden', count === 0);
            
            // Add animation
            badge.classList.add('animate-pulse');
            setTimeout(() => {
                badge.classList.remove('animate-pulse');
            }, 500);
        });
    }

    // ====================
    // 9. UTILITY METHODS
    // ====================
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

// ====================
// INITIALIZE APP
// ====================
document.addEventListener('DOMContentLoaded', () => {
    // Check if app already exists
    if (!window.app) {
        window.app = new FadliMartApp();
    }
    
    // Hide loading when page fully loads
    window.addEventListener('load', () => {
        setTimeout(() => window.app?.hideLoading(), 500);
    });
    
    // Auto-hide loading after 3s max (fallback)
    setTimeout(() => window.app?.hideLoading(), 3000);
    
    // Handle form submissions for forms without AJAX
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form.method === 'post' && !form.classList.contains('cart-form')) {
            window.app?.showLoading();
        }
    });
});
</script>
    
    @yield('scripts')
</body>
</html>