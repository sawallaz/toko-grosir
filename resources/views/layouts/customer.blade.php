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
    
    <!-- Improved Styles -->
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Safe area for modern phones */
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0); }
        
        /* Smooth transitions */
        * { transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease; }
        
        /* Custom scroll for desktop */
        @media (min-width: 768px) {
            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: #f1f5f9; }
            ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
            ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        }
        
        /* Loading states */
        .loading { opacity: 0.6; pointer-events: none; }
        
        /* Enhanced focus states */
        .focus-ring:focus {
            outline: 2px solid #4f46e5;
            outline-offset: 2px;
        }
    </style>
    
    <!-- Additional Meta -->
    <meta name="theme-color" content="#4f46e5">
    <meta name="description" content="Toko grosir online dengan harga termurah dan kualitas terbaik">
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased min-h-screen flex flex-col">

    <!-- HEADER / NAVBAR (Enhanced) -->
    <header class="bg-white shadow-sm sticky top-0 z-50 border-b border-gray-100">
        <div class="container mx-auto px-4 h-16 flex items-center justify-between gap-4">
            
            <!-- LOGO (Improved) -->
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

            <!-- SEARCH BAR (Enhanced) -->
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

            <!-- ACTION BUTTONS (Enhanced) -->
            <div class="flex items-center gap-2 md:gap-4 flex-shrink-0">
                
                <!-- Cart Button (Improved) -->
                <a href="{{ route('cart.index') }}" class="relative p-2 rounded-xl hover:bg-gray-50 transition-colors group focus-ring" aria-label="Keranjang belanja">
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

                <!-- Auth Section (Enhanced) -->
                <div class="flex-shrink-0">
                    @auth
                    <!-- User Dropdown (Improved) -->
                    <div x-data="{ open: false }" class="relative">
                        <!-- Trigger Button -->
                        <button 
                            @click="open = !open" 
                            class="flex items-center gap-2 hover:bg-gray-50 rounded-xl p-2 transition-all focus-ring"
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

                        <!-- Dropdown Menu (Enhanced) -->
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
                            
                            <!-- User Info -->
                            <div class="px-4 py-3 border-b border-gray-100">
                                <div class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500 capitalize mt-1 flex items-center gap-1">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    {{ Auth::user()->role }}
                                </div>
                            </div>

                            <!-- Menu Items -->
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

                            <!-- Logout -->
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
                    <!-- Guest State (Improved) -->
                    <div class="flex gap-2">
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-bold text-indigo-600 border border-indigo-600 rounded-xl hover:bg-indigo-50 transition-all focus-ring">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all shadow-sm hover:shadow focus-ring hidden md:block">
                            Daftar
                        </a>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT (Enhanced) -->
    <main class="flex-1 container mx-auto px-4 py-6">
        <!-- Flash Messages -->
        @if(session('success') || session('error') || session('warning'))
        <div class="mb-6 space-y-3">
            @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-medium flex items-center gap-3 animate-fade-in">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('success') }}
            </div>
            @endif
            
            @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium flex items-center gap-3 animate-fade-in">
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

    <!-- BOTTOM NAVIGATION (Enhanced) -->
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

            <!-- PESANAN SAYA - POSISI BARU -->
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
                @if(request()->routeIs('cart.*'))
                <div class="absolute top-0 w-1 h-1 bg-indigo-600 rounded-full"></div>
                @endif
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
                @if(request()->routeIs('profile.*'))
                <div class="absolute top-0 w-1 h-1 bg-indigo-600 rounded-full"></div>
                @endif
            </a>
        </div>
    </nav>

    <!-- Loading Overlay (Optional) -->
    <div id="global-loading" class="fixed inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-50 transition-opacity duration-300 opacity-0 pointer-events-none">
        <div class="text-center">
            <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600 font-medium">Memuat...</p>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Global loading handler
        document.addEventListener('DOMContentLoaded', function() {
            // Handle form submissions for global loading
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.method === 'post' || form.method === 'POST') {
                    showLoading();
                }
            });

            // Handle navigation for SPA-like experience
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link && link.href && !link.target && !link.hasAttribute('download')) {
                    const href = link.getAttribute('href');
                    if (href && href.startsWith('/') && !href.includes('#')) {
                        showLoading();
                    }
                }
            });

            function showLoading() {
                const loader = document.getElementById('global-loading');
                if (loader) {
                    loader.classList.remove('opacity-0', 'pointer-events-none');
                    loader.classList.add('opacity-100');
                }
            }

            function hideLoading() {
                const loader = document.getElementById('global-loading');
                if (loader) {
                    loader.classList.remove('opacity-100');
                    loader.classList.add('opacity-0', 'pointer-events-none');
                }
            }

            // Hide loading when page fully loads
            window.addEventListener('load', hideLoading);
            
            // Auto-hide loading after 3s max (fallback)
            setTimeout(hideLoading, 3000);
        });
    </script>
    
    <!-- Additional yield for page-specific scripts -->
    @yield('scripts')
</body>
</html>