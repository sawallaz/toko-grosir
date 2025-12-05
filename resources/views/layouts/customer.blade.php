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
    
    <!-- MIDTRANS SNAP (Wajib) -->
    <script type="text/javascript"
      src="https://app.sandbox.midtrans.com/snap/snap.js"
      data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        @media (max-width: 768px) { body { padding-bottom: 70px; } }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">

    <!-- HEADER -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center gap-1">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                <div class="font-black text-xl tracking-tighter leading-none"><span class="text-indigo-600">FADLI</span>MART</div>
            </a>

            <!-- Search Bar -->
            <div class="flex-1 max-w-2xl">
                <form action="{{ route('home') }}" method="GET" class="relative">
                    <input type="search" name="search" value="{{ request('search') }}" 
                           class="w-full bg-gray-100 border-transparent focus:bg-white border focus:border-indigo-500 rounded-lg py-2.5 pl-10 pr-4 text-sm transition-all" 
                           placeholder="Cari barang...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </form>
            </div>

            <!-- Menu Kanan -->
            <div class="flex items-center gap-3 md:gap-6">
                <a href="{{ route('cart.index') }}" class="relative group">
                    <svg class="w-7 h-7 text-gray-500 group-hover:text-indigo-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    @auth
                        @php $cartCount = count(session('cart', [])); @endphp
                        @if($cartCount > 0)
                            <span class="absolute -top-2 -right-2 bg-red-600 text-white text-[10px] font-bold h-5 w-5 flex items-center justify-center rounded-full border-2 border-white">{{ $cartCount }}</span>
                        @endif
                    @endauth
                </a>

                <div class="flex-shrink-0">
                    @auth
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 hover:bg-gray-50 rounded-lg p-1 pr-3 transition">
                            <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold border border-indigo-200">{{ substr(Auth::user()->name, 0, 1) }}</div>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-bold bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Masuk</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-6 min-h-screen">
        @yield('content')
    </main>

    <!-- Bottom Nav Mobile -->
    <div class="md:hidden fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 z-50 pb-safe shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
        <div class="grid grid-cols-4 h-16">
            <a href="{{ route('home') }}" class="flex flex-col items-center justify-center text-gray-400 hover:text-indigo-600 {{ request()->routeIs('home') ? 'text-indigo-600' : '' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[10px] font-bold">Beranda</span>
            </a>
            <!-- Link lainnya... (Sama seperti sebelumnya) -->
            <a href="{{ route('cart.index') }}" class="flex flex-col items-center justify-center text-gray-400 hover:text-indigo-600">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                <span class="text-[10px] font-bold">Keranjang</span>
            </a>
            <a href="{{ route('orders.index') }}" class="flex flex-col items-center justify-center text-gray-400 hover:text-indigo-600">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <span class="text-[10px] font-bold">Pesanan</span>
            </a>
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center text-gray-400 hover:text-indigo-600">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="text-[10px] font-bold">Akun</span>
            </a>
        </div>
    </div>
</body>
</html>