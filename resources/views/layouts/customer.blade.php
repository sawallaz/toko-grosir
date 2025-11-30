<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Toko Grosir') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        /* Padding bawah hanya untuk mobile agar tidak tertutup bottom nav */
        @media (max-width: 768px) { body { padding-bottom: 70px; } }
    </style>
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">

    <!-- HEADER / NAVBAR (Sticky Top) -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 h-16 flex items-center justify-between gap-4">
            
            <!-- 1. LOGO -->
            <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center gap-1">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                <div class="font-black text-xl tracking-tighter leading-none">
                    <span class="text-indigo-600">FADLI</span>MART
                </div>
            </a>

            <!-- 2. SEARCH BAR (Desktop & Mobile) -->
            <div class="flex-1 max-w-2xl">
                <form action="{{ route('home') }}" method="GET" class="relative">
                    <input type="search" name="search" value="{{ request('search') }}" 
                           class="w-full bg-gray-100 border-transparent focus:bg-white border focus:border-indigo-500 rounded-lg py-2.5 pl-10 pr-4 text-sm transition-all" 
                           placeholder="Cari barang murah disini...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </form>
            </div>

            <!-- 3. MENU KANAN (Cart & Auth) -->
            <div class="flex items-center gap-3 md:gap-6">
                
                <!-- Keranjang -->
                <a href="{{ route('cart.index') }}" class="relative group">
                    <svg class="w-7 h-7 text-gray-500 group-hover:text-indigo-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    @auth
                        @php $cartCount = \App\Models\Transaction::where('buyer_id', Auth::id())->where('status', 'pending')->where('type', 'online')->first()?->details->sum('quantity') ?? 0; @endphp
                        @if($cartCount > 0)
                            <span class="absolute -top-2 -right-2 bg-red-600 text-white text-[10px] font-bold h-5 w-5 flex items-center justify-center rounded-full border-2 border-white">{{ $cartCount }}</span>
                        @endif
                    @endauth
                </a>

                <div class="h-6 w-px bg-gray-300 hidden md:block"></div>

                <!-- Auth Menu -->
                <div class="flex-shrink-0">
                    @auth
    <!-- Sudah Login dengan Dropdown -->
    <div x-data="{ open: false }" class="relative">
        <!-- Tombol Profil -->
        <button @click="open = !open" class="flex items-center gap-2 hover:bg-gray-50 rounded-lg p-1 pr-3 transition focus:outline-none">
            <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold border border-indigo-200">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="hidden md:block text-left">
                <div class="text-xs text-gray-500 font-bold">Halo,</div>
                <div class="text-sm font-bold text-gray-800 leading-none truncate max-w-[100px]">{{ Auth::user()->name }}</div>
            </div>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div x-show="open" 
             @click.away="open = false"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
            
            <!-- Info User -->
            <div class="px-4 py-2 border-b border-gray-100">
                <div class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</div>
                <div class="text-xs text-gray-500 capitalize">{{ Auth::user()->role }}</div>
            </div>

            <!-- Pengaturan -->
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Pengaturan
            </a>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </div>
@else
                        <!-- Belum Login -->
                        <div class="flex gap-2">
                            <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-bold text-indigo-600 border border-indigo-600 rounded-lg hover:bg-indigo-50 transition">Masuk</a>
                            <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition hidden md:block">Daftar</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- CONTENT UTAMA -->
    <main class="container mx-auto px-4 py-6 min-h-screen">
        @yield('content')
    </main>

    <!-- BOTTOM NAVIGATION (HANYA DI HP / Mobile Only) -->
    <div class="md:hidden fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 z-50 pb-safe shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
        <div class="grid grid-cols-4 h-16">
            <a href="{{ route('home') }}" class="flex flex-col items-center justify-center text-gray-400 hover:text-indigo-600 {{ request()->routeIs('home') ? 'text-indigo-600' : '' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[10px] font-bold">Beranda</span>
            </a>
            <a href="#" class="flex flex-col items-center justify-center text-gray-400 hover:text-indigo-600">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="text-[10px] font-bold">Kategori</span>
            </a>
            <a href="{{ route('cart.index') }}" class="flex flex-col items-center justify-center text-gray-400 hover:text-indigo-600 relative">
                <div class="relative">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    @auth
                        @if($cartCount > 0) <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full">{{ $cartCount }}</span> @endif
                    @endauth
                </div>
                <span class="text-[10px] font-bold">Keranjang</span>
            </a>
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center text-gray-400 hover:text-indigo-600 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : '' }}">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="text-[10px] font-bold">Akun</span>
            </a>
        </div>
    </div>

</body>
</html>