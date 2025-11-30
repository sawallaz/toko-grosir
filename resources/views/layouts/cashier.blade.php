<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir - {{ config('app.name', 'Fadli Fajar POS') }}</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        html, body { height: 100%; margin: 0; overflow: hidden; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c7c7c7; border-radius: 3px; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <!-- Form Logout Tersembunyi -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <div class="flex h-screen w-full bg-gray-100 font-sans overflow-hidden" x-data="posSystem()">
        
        <!-- SIDEBAR KIRI -->
        <div class="w-64 bg-indigo-900 text-white flex flex-col shadow-2xl z-50 flex-shrink-0">
            <!-- Header Sidebar -->
            <div class="p-4 border-b border-indigo-800 bg-indigo-800/50">
                <!-- Profil Kasir Compact -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 bg-indigo-700 rounded-full flex items-center justify-center font-bold text-sm">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-white text-sm truncate">{{ Auth::user()->name }}</div>
                        <div class="text-indigo-200 text-xs">Kasir</div>
                    </div>
                    <button onclick="logout()" class="text-indigo-300 hover:text-white transition text-xs" title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </div>

                <!-- Waktu Real-time Compact -->
                <div class="bg-indigo-900/50 p-3 rounded border border-indigo-700">
                    <div class="text-center">
                        <div class="text-[9px] text-indigo-300 uppercase font-bold tracking-wider mb-1">Waktu</div>
                        <div class="text-lg font-mono font-bold text-white" x-text="currentTime"></div>
                        <div class="text-[10px] text-indigo-300 truncate" x-text="currentDate"></div>
                    </div>
                </div>
            </div>

            <!-- Informasi Transaksi Compact -->
            <div class="p-4 space-y-3">
                <!-- Invoice Aktif -->
                <div class="bg-indigo-900/50 p-3 rounded border border-indigo-700">
                    <div class="text-[9px] text-indigo-300 uppercase font-bold tracking-wider mb-1">No. Transaksi</div>
                    <div class="font-mono text-sm font-bold text-white tracking-tight truncate" x-text="invoiceNumber"></div>
                </div>

                <!-- Info Customer -->
                <div class="bg-indigo-900/50 p-3 rounded border border-indigo-700">
                    <div class="text-[9px] text-indigo-300 uppercase font-bold tracking-wider mb-1">Pelanggan</div>
                    <div class="text-xs font-bold text-white truncate" x-text="selectedCustomer ? selectedCustomer.name : 'Pelanggan Umum'"></div>
                    <div class="text-[10px] text-indigo-300 truncate" x-text="selectedCustomer ? (selectedCustomer.phone || '-') : '-'"></div>
                </div>

                <!-- Total Tagihan -->
                <div class="bg-indigo-900/50 p-3 rounded border border-indigo-700">
                    <div class="text-[9px] text-indigo-300 uppercase font-bold tracking-wider mb-1">Total Tagihan</div>
                    <div class="text-lg font-black text-white truncate" x-text="formatRupiah(grandTotal)"></div>
                </div>
            </div>

            <!-- Input Customer Compact -->
            <div class="p-4 border-t border-indigo-800">
                <div class="text-[9px] text-indigo-300 uppercase font-bold tracking-wider mb-2">Cari Member</div>
                <div class="space-y-2">
                    <div class="flex gap-1">
                        <div class="relative flex-1">
                            <input type="text" 
                                x-model="customerSearchInput" 
                                @keydown.enter.prevent="findCustomer()" 
                                :disabled="selectedCustomer !== null"
                                class="w-full border-indigo-600 bg-indigo-800 text-white rounded text-xs h-8 focus:ring-green-500 focus:border-green-500 disabled:bg-green-600 disabled:text-white placeholder-indigo-300 pl-2 pr-6" 
                                placeholder="HP / Nama...">
                            <button x-show="selectedCustomer" 
                                    @click="resetCustomer()" 
                                    class="absolute inset-y-0 right-0 pr-1 text-red-400 hover:text-red-200 text-xs">
                                ✕
                            </button>
                        </div>
                        <button @click="findCustomer()" 
                                x-show="!selectedCustomer" 
                                class="bg-indigo-600 px-2 h-8 rounded text-indigo-200 hover:bg-indigo-500 hover:text-white text-xs">
                            🔍
                        </button>
                    </div>
                    <button @click="openCustomerModal()" 
                            class="w-full bg-green-600 text-white py-1.5 rounded font-bold hover:bg-green-500 text-xs flex items-center justify-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Kelola Member
                    </button>
                </div>
            </div>

            <!-- Navigation Menu Compact -->
            <div class="flex-1 py-3 space-y-1 px-4">
                <button @click="switchTab('sales')" 
                        :class="{'bg-indigo-600 text-white shadow-lg ring-1 ring-white/20': tab === 'sales', 'text-indigo-300 hover:bg-indigo-800 hover:text-white': tab !== 'sales'}"
                        class="w-full flex items-center px-3 py-2 rounded text-xs font-bold transition-all duration-200 group">
                    <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    TRANSAKSI [F1]
                </button>
                
                <button @click="switchTab('history')" 
                        :class="{'bg-indigo-600 text-white shadow-lg ring-1 ring-white/20': tab === 'history', 'text-indigo-300 hover:bg-indigo-800 hover:text-white': tab !== 'history'}"
                        class="w-full flex items-center px-3 py-2 rounded text-xs font-bold transition-all duration-200 group">
                    <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    RIWAYAT [F2]
                </button>

                <button @click="switchTab('online')" 
                        :class="{'bg-indigo-600 text-white shadow-lg ring-1 ring-white/20': tab === 'online', 'text-indigo-300 hover:bg-indigo-800 hover:text-white': tab !== 'online'}"
                        class="w-full flex items-center px-3 py-2 rounded text-xs font-bold transition-all duration-200 group relative">
                    <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9-3-9m-9 9a9 9 0 019-9"></path>
                    </svg>
                    ONLINE
    <!-- [FIX] Cara paling aman -->
    @if(($pendingOrders->total() ?? 0) > 0) 
        <span class="absolute right-3 bg-red-500 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full shadow-sm animate-pulse">{{ $pendingOrders->total() ?? 0 }}</span> 
    @endif
                </button>
            </div>
        </div>

        <!-- KONTEN UTAMA -->
        <div class="flex-1 flex flex-col h-full relative min-w-0 bg-gray-100 overflow-hidden">
            @yield('content')
        </div>

    </div>

    <script>
       // LEBIH AMAN - validasi tambahan
function logout() {
    if(confirm('Yakin ingin logout?')) {
        const form = document.getElementById('logout-form');
        if(form) {
            form.submit();
        } else {
            console.error('Logout form not found');
            // Fallback - redirect manual dengan CSRF
            window.location.href = '{{ route("logout") }}';
        }
    }
}
    </script>
</body>
</html>