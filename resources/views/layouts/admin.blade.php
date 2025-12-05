<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Style kustom untuk scrollbar (opsional, tapi bikin keren) -->
    <style>
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-100">
        <!-- A. Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-30 flex flex-col h-full w-64 transform bg-gray-800 text-white transition-transform duration-300 ease-in-out lg:translate-x-0"
            :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
            
            <!-- Logo/Nama Toko -->
            <div class="flex items-center justify-center h-16 px-6 bg-gray-900">
                <span class="text-2xl font-bold tracking-wide">Fadli Fajar POS</span>
            </div>

            <!-- Navigasi Sidebar -->
            <nav class="flex-1 overflow-y-auto" x-data="{ openMenu: '' }">
                <ul class="py-4">
                    <!-- Item: Dashboard -->
                    <li>
                        <a href="{{ route('admin.dashboard') }}"
                           class="flex items-center px-6 py-3 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                            <!-- Ikon Dashboard -->
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25A2.25 2.25 0 0113.5 8.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                            <span class="ml-3">Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Item: Manajemen Produk -->
                    <li>
                        <a href="{{ route('admin.products.index') }}"
                           class="flex items-center px-6 py-3 {{ request()->routeIs('admin.products.*') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                            <!-- Ikon Produk -->
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                            <span class="ml-3">Manajemen Produk</span>
                        </a>
                    </li>

                    <!-- Item: Manajemen Stok -->
                    <li>
                        <!-- [DIUBAH] Arahkan ke rute stok.index -->
                        <a href="{{ route('admin.stok.index') }}"
                           class="flex items-center px-6 py-3 {{ request()->routeIs('admin.stok.*') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                            <!-- Ikon Stok -->
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                            <span class="ml-3">Manajemen Stok</span>
                        </a>
                    </li>

                    <!-- Item: Manajemen Kasir -->
                    <li>
                        <a href="{{ route('admin.cashiers.index') }}" class="flex items-center px-6 py-3 hover:bg-gray-700">
                            <!-- Ikon Kasir -->
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            <span class="ml-3">Manajemen Kasir</span>
                        </a>
                    </li>

                    <!-- Item: Laporan Penjualan -->
                    <li>
                        <a href="{{ route('admin.reports.sales') }}" class="flex items-center px-6 py-3 hover:bg-gray-700">
                            <!-- Ikon Laporan -->
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                            </svg>
                            <span class="ml-3">Laporan Penjualan</span>
                        </a>
                    </li>

                    <!-- Item: Pesanan Online -->
                    <li>
                        <a href="#" class="flex items-center px-6 py-3 hover:bg-gray-700">
                            <!-- Ikon Pesanan Online -->
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <span class="ml-3">Pesanan Online</span>
                        </a>
                    </li>

                    <!-- Garis Pemisah -->
                    <li class="px-6 py-4">
                        <hr class="border-gray-700">
                    </li>

                    <!-- Item: Tampilan Customer -->
                    <li>
                        <a href="{{ route('home') }}" target="_blank" class="flex items-center px-6 py-3 hover:bg-gray-700">
                            <!-- Ikon Tampilan Customer -->
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-4.5 0V6a1.5 1.5 0 00-1.5-1.5h-3a1.5 1.5 0 00-1.5 1.5v4.5m4.5 0h-4.5" />
                            </svg>
                            <span class="ml-3">Tampilan Customer</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Overlay untuk Mobile (saat sidebar terbuka) -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black opacity-50 lg:hidden" x-cloak></div>

        <!-- B. Konten Utama -->
        <div class="flex-1 flex flex-col overflow-hidden lg:pl-64">
            
            <!-- 1. Header/Navbar Atas -->
            <header class="sticky top-0 z-10 flex items-center justify-between h-16 px-6 bg-white shadow-sm">
                <!-- Tombol Hamburger (Mobile) -->
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 lg:hidden">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Judul Halaman (diambil dari section 'header') -->
                <div class="flex-1">
                    @yield('header')
                </div>

                <!-- Dropdown Profil (diambil dari Breeze) -->
                <div class="hidden sm:flex sm:items-center sm:ml-6">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                
                                <!-- [PERUBAHAN] IKON PROFIL DITAMBAHKAN DI SINI -->
                                <svg class="mr-2 h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ml-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <!-- Link Pengaturan/Profil -->
                            <x-dropdown-link :href="url('/profile')">
                                {{ __('Pengaturan Profil') }}
                            </x-dropdown-link>

                            <!-- Tombol Logout -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </header>

            <!-- 2. Konten Halaman Utama -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="container mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>