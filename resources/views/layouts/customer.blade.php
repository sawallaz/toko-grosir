<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Toko Grosir') }}</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- MIDTRANS SNAP (Wajib untuk Pembayaran Online) -->
    <script type="text/javascript"
      src="https://app.sandbox.midtrans.com/snap/snap.js"
      data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        /* Padding bawah agar tidak ketutup Bottom Nav di HP */
        body { padding-bottom: 80px; } 
        @media (min-width: 768px) { body { padding-bottom: 0; } }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased selection:bg-indigo-100 selection:text-indigo-700">

    <!-- [PERBAIKAN UTAMA] Hitung Cart Count Global di Sini -->
    @php
        $cartCount = 0;
        if(Auth::check()) {
            // Hitung jumlah item di keranjang yang statusnya pending (belum checkout/masih cart)
            // Disini kita asumsikan status 'pending' adalah cart aktif untuk online
            // Atau sesuaikan logika jika Anda punya status khusus 'cart'
            $cartTransaction = \App\Models\Transaction::where('buyer_id', Auth::id())
                ->where('status', 'pending')
                ->where('type', 'online')
                ->first();
            
            if($cartTransaction) {
                $cartCount = $cartTransaction->details->sum('quantity');
            } else {
                // Jika pakai session cart (sebelum checkout)
                $sessionCart = session('cart', []);
                foreach($sessionCart as $item) {
                    $cartCount += $item['quantity'];
                }
            }
        }
    @endphp

    <!-- HEADER (DESKTOP & MOBILE) -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="container mx-auto px-4 h-16 flex items-center justify-between gap-6">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-1 flex-shrink-0 group">
                <div class="bg-indigo-600 text-white p-1.5 rounded-lg group-hover:rotate-12 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <div class="leading-none">
                    <div class="font-black text-xl tracking-tighter text-gray-900">FADLY<span class="text-indigo-600">FAJAR</span></div>
                </div>
            </a>

            <!-- Search Bar (Desktop) -->
            <div class="hidden md:block flex-1 max-w-xl">
                <form action="{{ route('home') }}" method="GET" class="relative">
                    <input type="search" name="search" value="{{ request('search') }}" 
                           class="w-full bg-gray-100 border-transparent focus:bg-white border focus:border-indigo-500 rounded-full py-2.5 pl-11 pr-4 text-sm transition-all shadow-sm" 
                           placeholder="Cari produk murah...">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </form>
            </div>

            <!-- Menu Kanan -->
            <div class="flex items-center gap-3 md:gap-6">
                
                <!-- Keranjang (Desktop) -->
                <a href="{{ route('cart.index') }}" class="relative group hidden md:block">
                    <div class="p-2 rounded-full group-hover:bg-gray-100 transition">
                        <svg class="w-6 h-6 text-gray-600 group-hover:text-indigo-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        @if($cartCount > 0)
                            <span class="absolute top-0 right-0 bg-red-600 text-white text-[10px] font-bold h-5 w-5 flex items-center justify-center rounded-full border-2 border-white shadow-sm">{{ $cartCount }}</span>
                        @endif
                    </div>
                </a>

                <div class="h-6 w-px bg-gray-200 hidden md:block"></div>

                <!-- Auth Menu -->
                <div class="flex-shrink-0">
                    @auth
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 hover:bg-gray-50 rounded-xl p-1 pr-3 transition group">
                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold shadow-sm text-sm border-2 border-white ring-2 ring-gray-100 group-hover:ring-indigo-200">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div class="hidden md:block text-left">
                                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Akun</div>
                                <div class="text-sm font-bold text-gray-800 leading-none truncate max-w-[100px]">{{ Auth::user()->name }}</div>
                            </div>
                        </a>
                    @else
                        <div class="flex gap-2">
                            <a href="{{ route('login') }}" class="px-5 py-2 text-sm font-bold text-indigo-600 border border-indigo-600 rounded-full hover:bg-indigo-50 transition">Masuk</a>
                            <a href="{{ route('register') }}" class="hidden md:inline-block px-5 py-2 text-sm font-bold text-white bg-indigo-600 rounded-full hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition">Daftar</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

   <!-- GANTI CONTENT SECTION DI MAIN -->
    <main class="container mx-auto px-4 py-6 min-h-[80vh]">
        <!-- SEARCH BAR MOBILE YANG TAMBAHAN (backup) -->
        @yield('mobile-search')
        
        <!-- YIELD CONTENT UTAMA -->
        @yield('content')
    </main>

    <!-- BOTTOM NAVIGATION (KHUSUS MOBILE) -->
<nav class="md:hidden fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 z-50 pb-safe shadow-[0_-4px_20px_rgba(0,0,0,0.03)] rounded-t-2xl" 
     x-data="{ activeTab: '{{ request()->routeIs('home') ? 'home' : (request()->routeIs('cart.*') ? 'cart' : (request()->routeIs('orders.*') ? 'orders' : 'home')) }}' }">
    <div class="grid grid-cols-4 h-16 items-center">
        
        <!-- 1. Home -->
        <a href="{{ route('home') }}" 
           @click="activeTab = 'home'"
           class="flex flex-col items-center justify-center gap-1 transition-colors"
           :class="activeTab === 'home' ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600'">
            <div class="relative">
                <svg class="w-6 h-6" :class="activeTab === 'home' ? 'fill-indigo-100' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
            </div>
            <span class="text-[10px] font-bold" :class="activeTab === 'home' ? 'text-indigo-600' : 'text-gray-500'">Beranda</span>
        </a>
        
        <!-- 3. Keranjang -->
        <a href="{{ route('cart.index') }}" 
           @click="activeTab = 'cart'"
           class="flex flex-col items-center justify-center gap-1 relative transition-colors"
           :class="activeTab === 'cart' ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600'">
            <div class="relative">
                <svg class="w-6 h-6" :class="activeTab === 'cart' ? 'fill-indigo-100' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                @auth
                    @if($cartCount > 0) 
                        <span class="absolute -top-1.5 -right-1.5 bg-red-600 text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full border border-white shadow-sm">{{ $cartCount }}</span> 
                    @endif
                @endauth
            </div>
            <span class="text-[10px] font-bold" :class="activeTab === 'cart' ? 'text-indigo-600' : 'text-gray-500'">Keranjang</span>
        </a>

        <!-- 4. Pesanan / Akun -->
        <a href="{{ route('orders.index') }}" 
           @click="activeTab = 'orders'"
           class="flex flex-col items-center justify-center gap-1 transition-colors"
           :class="activeTab === 'orders' ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600'">
            <svg class="w-6 h-6" :class="activeTab === 'orders' ? 'fill-indigo-100' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <span class="text-[10px] font-bold" :class="activeTab === 'orders' ? 'text-indigo-600' : 'text-gray-500'">Pesanan</span>
        </a>
    </div>
</nav>


</body>
</html>