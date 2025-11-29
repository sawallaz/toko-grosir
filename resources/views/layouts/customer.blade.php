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
    <style>
        body { padding-bottom: 80px; } /* Ruang untuk Bottom Nav */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">

    <!-- HEADER (FIXED TOP) -->
    <div class="bg-white shadow-sm sticky top-0 z-50 border-b border-gray-200">
        <div class="max-w-md mx-auto px-4 h-14 flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-black text-xl text-indigo-700 tracking-tighter flex items-center gap-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                FADLI<span class="text-gray-800">MART</span>
            </a>
            
            <div class="flex items-center gap-3">
                @auth
                    <a href="#" class="text-xs font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded-full">
                        {{ Auth::user()->name }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-bold bg-indigo-600 text-white px-3 py-1.5 rounded-full shadow hover:bg-indigo-700">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- CONTENT -->
    <main class="max-w-md mx-auto min-h-screen">
        @yield('content')
    </main>

    <!-- BOTTOM NAVIGATION (FIXED BOTTOM) -->
    <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 z-50 safe-area-pb shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
        <div class="max-w-md mx-auto grid grid-cols-4 h-16 relative">
            
            <!-- Home -->
            <a href="{{ route('home') }}" class="flex flex-col items-center justify-center gap-1 transition {{ request()->routeIs('home') ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[10px] font-bold">Beranda</span>
            </a>

            <!-- Cari (Kategori) -->
            <a href="#" onclick="document.getElementById('search-input').focus(); return false;" class="flex flex-col items-center justify-center gap-1 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <span class="text-[10px] font-bold">Cari</span>
            </a>

            <!-- Keranjang -->
            <a href="{{ route('cart.index') }}" class="flex flex-col items-center justify-center gap-1 transition {{ request()->routeIs('cart.*') ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600' }} relative">
                <div class="relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    @auth
                        @php $cartCount = \App\Models\Transaction::where('buyer_id', Auth::id())->where('status', 'cart')->first()?->details->sum('quantity') ?? 0; @endphp
                        @if($cartCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 bg-red-600 text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full border border-white">{{ $cartCount }}</span>
                        @endif
                    @endauth
                </div>
                <span class="text-[10px] font-bold">Keranjang</span>
            </a>

            <!-- Akun / Pesanan -->
            <a href="{{ route('orders.index') }}" class="flex flex-col items-center justify-center gap-1 transition {{ request()->routeIs('orders.*') ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <span class="text-[10px] font-bold">Pesanan</span>
            </a>

        </div>
    </div>
</body>
</html>