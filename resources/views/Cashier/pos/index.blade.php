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
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-fadeIn { animation: fadeIn 0.2s ease-out; }
        
        /* Custom scrollbar for modals */
        .modal-scrollbar::-webkit-scrollbar { width: 8px; }
        .modal-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .modal-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
        .modal-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <!-- Form Logout Tersembunyi -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <div class="flex h-screen w-full bg-gray-100 font-sans overflow-hidden" x-data="posSystem()" x-init="init()">
        
        <!-- SIDEBAR KIRI -->
        <div class="w-64 bg-gradient-to-b from-indigo-900 to-indigo-800 text-white flex flex-col shadow-2xl z-50 flex-shrink-0">
            <!-- Header Sidebar -->
            <div class="p-4 border-b border-indigo-700 bg-indigo-800/50">
                <!-- Profil Kasir -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center font-bold text-lg">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-white text-sm truncate">{{ Auth::user()->name }}</div>
                        <div class="text-indigo-200 text-xs">Kasir</div>
                    </div>
                    <button onclick="logout()" class="text-indigo-300 hover:text-white transition" title="Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 013-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </div>

                <!-- Waktu Real-time -->
                <div class="bg-indigo-800/60 p-3 rounded-lg border border-indigo-700">
                    <div class="text-center">
                        <div class="text-xs text-indigo-300 uppercase font-bold tracking-wider mb-1">Waktu</div>
                        <div class="text-xl font-mono font-bold text-white" x-text="currentTime"></div>
                        <div class="text-xs text-indigo-300 truncate" x-text="currentDate"></div>
                    </div>
                </div>
            </div>

            <!-- Informasi Transaksi -->
            <div class="p-4 space-y-4">
                <!-- Invoice Aktif -->
                <div class="bg-indigo-800/60 p-3 rounded-lg border border-indigo-700">
                    <div class="text-xs text-indigo-300 uppercase font-bold tracking-wider mb-1">No. Transaksi</div>
                    <div class="font-mono text-sm font-bold text-white tracking-tight truncate" x-text="lastInvoiceNumber || '-'"></div>
                </div>

                <!-- Info Customer -->
                <div class="bg-indigo-800/60 p-3 rounded-lg border border-indigo-700">
                    <div class="text-xs text-indigo-300 uppercase font-bold tracking-wider mb-1">Pelanggan</div>
                    <div class="text-sm font-bold text-white truncate" x-text="selectedCustomer ? selectedCustomer.name : 'Pelanggan Umum'"></div>
                    <div class="text-xs text-indigo-300 truncate" x-text="selectedCustomer ? (selectedCustomer.phone || '-') : '-'"></div>
                </div>

                <!-- Total Tagihan -->
                <div class="bg-gradient-to-r from-indigo-700 to-purple-700 p-3 rounded-lg border border-indigo-600 shadow-lg">
                    <div class="text-xs text-indigo-200 uppercase font-bold tracking-wider mb-1">Total Tagihan</div>
                    <div class="text-2xl font-black text-white truncate" x-text="formatRupiah(grandTotal)"></div>
                </div>
            </div>

            <!-- Input Customer -->
            <div class="p-4 border-t border-indigo-700">
                <div class="text-xs text-indigo-300 uppercase font-bold tracking-wider mb-2">Cari Member</div>
                <div class="space-y-3">
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" 
                                x-model="customerSearchInput" 
                                @keydown.enter.prevent="findCustomer()" 
                                :disabled="selectedCustomer !== null"
                                class="w-full border-indigo-600 bg-indigo-800 text-white rounded-lg text-sm h-9 focus:ring-green-500 focus:border-green-500 disabled:bg-green-600 disabled:text-white placeholder-indigo-300 pl-3 pr-8" 
                                placeholder="HP / Nama..." 
                                @keydown.f8="openCustomerModal()">
                            <button x-show="selectedCustomer" 
                                    @click="resetCustomer()" 
                                    class="absolute inset-y-0 right-0 pr-2 text-red-400 hover:text-red-200">
                                ✕
                            </button>
                        </div>
                        <button @click="findCustomer()" 
                                x-show="!selectedCustomer" 
                                class="bg-indigo-600 px-3 h-9 rounded-lg text-white hover:bg-indigo-500 text-sm font-bold">
                            Cari
                        </button>
                    </div>
                    <button @click="openCustomerModal()" 
                            class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-2 rounded-lg font-bold hover:from-green-700 hover:to-emerald-700 text-sm flex items-center justify-center gap-2 shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Kelola Member
                    </button>
                </div>
            </div>

            <!-- Navigation Menu -->
            <div class="flex-1 py-4 space-y-2 px-4">
                <button @click="switchTab('sales')" 
                        :class="{'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg ring-2 ring-white/20': tab === 'sales', 'text-indigo-200 hover:bg-indigo-800/50 hover:text-white': tab !== 'sales'}"
                        class="w-full flex items-center px-4 py-3 rounded-lg text-sm font-bold transition-all duration-200 group">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    TRANSAKSI [F1]
                </button>
                
                <button @click="switchTab('history')" 
                        :class="{'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg ring-2 ring-white/20': tab === 'history', 'text-indigo-200 hover:bg-indigo-800/50 hover:text-white': tab !== 'history'}"
                        class="w-full flex items-center px-4 py-3 rounded-lg text-sm font-bold transition-all duration-200 group">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    RIWAYAT [F2]
                </button>

                <button @click="switchTab('online')" 
                        :class="{'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg ring-2 ring-white/20': tab === 'online', 'text-indigo-200 hover:bg-indigo-800/50 hover:text-white': tab !== 'online'}"
                        class="w-full flex items-center px-4 py-3 rounded-lg text-sm font-bold transition-all duration-200 relative">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9-3-9m-9 9a9 9 0 019-9"></path>
                    </svg>
                    ONLINE [F3]
                    <span x-show="pendingCount > 0" 
                          class="absolute right-4 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg">
                        <span x-text="pendingCount"></span>
                    </span>
                </button>
            </div>
        </div>

        <!-- KONTEN UTAMA -->
        <div class="flex-1 flex flex-col h-full relative min-w-0 bg-gray-100 overflow-hidden">
            <!-- TAB 1: SALES (TRANSAKSI) -->
            <div x-show="tab === 'sales'" class="h-full flex flex-col" style="display: none;">
                <!-- Tabel Input Produk -->
                <div class="flex-1 overflow-hidden flex flex-col">
                    <div class="flex-1 overflow-auto custom-scrollbar">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-gray-800 to-gray-900 text-white sticky top-0 z-10 shadow-lg">
                                <tr>
                                    <th class="px-6 py-4 text-left w-12 text-sm font-bold uppercase tracking-wider">#</th>
                                    <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">Produk</th>
                                    <th class="px-6 py-4 text-left w-36 text-sm font-bold uppercase tracking-wider">Satuan</th>
                                    <th class="px-6 py-4 text-center w-28 text-sm font-bold uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-4 text-right w-40 text-sm font-bold uppercase tracking-wider">Harga</th>
                                    <th class="px-6 py-4 text-right w-48 text-sm font-bold uppercase tracking-wider">Subtotal</th>
                                    <th class="px-6 py-4 text-center w-20 text-sm font-bold uppercase tracking-wider">Hapus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                            <template x-for="(row, index) in cart" :key="row.tempId">
                                <tr :class="{'bg-red-50/50': row.error, 'bg-indigo-50/30': row.active}" 
                                    class="group hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 text-center text-gray-500 font-bold" x-text="index + 1"></td>
                                    
                                    <!-- Search Input -->
                                    <td class="px-6 py-4 relative">
                                        <div class="relative">
                                            <input type="text" 
                                                x-model="row.product_name" 
                                                @input.debounce.300ms="searchProduct(index)" 
                                                @keydown.down.prevent="focusNextResult(index)"
                                                @keydown.up.prevent="focusPrevResult(index)"
                                                @keydown.enter.prevent="selectResult(index)"
                                                @keydown.escape="row.showResults = false"
                                                @focus="row.active = true; row.showResults = true" 
                                                @blur="setTimeout(() => { row.active = false; row.showResults = false; }, 200)"
                                                class="w-full border-2 border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-bold text-gray-800 placeholder-gray-400 h-10 pl-4 pr-10 shadow-sm transition-all" 
                                                placeholder="🔍 Scan barcode / ketik nama..." 
                                                autocomplete="off">
                                            
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        
                                        <!-- Dropdown Result -->
                                        <div x-show="row.showResults && row.results.length > 0" 
                                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 shadow-2xl rounded-lg max-h-80 overflow-y-auto left-0">
                                            <ul class="py-1">
                                                <template x-for="(res, resIdx) in row.results" :key="res.id">
                                                    <li @click="selectProduct(index, res)"  
                                                        class="px-4 py-3 cursor-pointer hover:bg-indigo-50 border-b last:border-b-0 transition-colors"
                                                        :class="{'bg-indigo-100': row.focusIndex === resIdx}">
                                                        <div class="flex justify-between items-center">
                                                            <div class="flex-1">
                                                                <div class="font-bold text-gray-800" x-text="res.name"></div>
                                                                <div class="text-xs text-gray-500 font-mono mt-1" 
                                                                     x-text="res.kode_produk"></div>
                                                                <div class="text-xs text-gray-400 mt-1" 
                                                                     x-text="res.category"></div>
                                                            </div>
                                                            <div class="text-right ml-4">
                                                                <span class="inline-block px-2 py-1 text-xs font-bold rounded" 
                                                                    :class="res.stock_total > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                                                    Stok: <span x-text="res.stock_total"></span>
                                                                </span>
                                                                <div class="font-bold text-indigo-600 text-sm mt-2" 
                                                                    x-text="formatRupiah(res.units[0]?.price || 0)"></div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </td>

                                    <!-- Satuan -->
                                    <td class="px-6 py-4">
                                        <div class="relative">
                                            <select x-model="row.product_unit_id" @change="updateUnit(index)" 
                                                    @keydown.enter="focusNextField(index, 'qty')"
                                                    class="w-full border-2 border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white h-10 pl-3 pr-8 appearance-none shadow-sm"
                                                    :disabled="!row.product_id">
                                                <template x-for="u in row.available_units" :key="u.product_unit_id">
                                                    <option :value="u.product_unit_id" 
                                                            x-text="u.unit_short_name + ' - ' + formatRupiah(u.price)"></option>
                                                </template>
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div x-show="row.isWholesale" class="text-xs text-green-600 font-bold italic mt-1">
                                            💰 Harga Grosir Aktif
                                        </div>
                                    </td>

                                    <!-- Qty -->
                                    <td class="px-6 py-4">
                                        <input type="number" x-model="row.qty" min="1" @change="updateSubtotal(index)"
                                            @keydown.enter="focusNextField(index, 'add')"
                                            class="w-full text-center border-2 border-gray-300 rounded-lg text-sm font-bold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 h-10 px-3 shadow-sm"
                                            :disabled="!row.product_id" placeholder="1">
                                    </td>

                                    <!-- Harga -->
                                    <td class="px-6 py-4 text-right align-middle">
                                        <span class="font-mono text-lg font-bold text-gray-700" 
                                              x-text="formatRupiah(row.price)"></span>
                                    </td>

                                    <!-- Subtotal -->
                                    <td class="px-6 py-4 text-right align-middle">
                                        <span class="font-mono text-xl font-black text-indigo-700" 
                                              x-text="formatRupiah(row.subtotal)"></span>
                                    </td>

                                    <!-- Hapus -->
                                    <td class="px-6 py-4 text-center align-middle">
                                        <button @click="removeRow(index)" 
                                                class="text-gray-400 hover:text-red-600 hover:bg-red-50 p-2 rounded-lg transition-all duration-200 transform hover:scale-110">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            
                            <!-- Baris kosong untuk tambah otomatis -->
                            <tr>
    <td colspan="7" class="p-3 text-center bg-gradient-to-r from-gray-50 to-white border-t border-gray-200">
        <button @click="addRow()" 
                class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white px-4 py-2.5 rounded-md font-semibold text-sm shadow transition-all duration-150 hover:bg-gradient-to-r hover:from-indigo-600 hover:to-purple-600 hover:shadow-md flex items-center gap-1 mx-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Baris Baru
        </button>
    </td>
</tr>
                        </tbody>
                        </table>
                    </div>
                </div>

                <!-- FOOTER PEMBAYARAN -->
                <div class="bg-gradient-to-r from-white to-gray-50 border-t border-gray-300 shadow-xl z-20 flex-shrink-0">
                    <div class="px-8 py-6">
                        <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
                            <div class="text-left">
                                <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Total Tagihan</div>
                                <div class="text-4xl lg:text-5xl font-black text-gray-900 tracking-tighter leading-none" 
                                     x-text="formatRupiah(grandTotal)"></div>
                                <div class="text-sm text-gray-500 mt-2">
                                    <span x-text="cart.filter(r => r.product_id).length"></span> item dalam keranjang
                                </div>
                            </div>
                            
                            <div class="flex flex-col lg:flex-row items-center gap-4 w-full lg:w-auto">
                                <!-- Input Pembayaran -->
                                <div class="relative w-full lg:w-72">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-400 font-bold">Rp</span>
                                    </div>
                                    <input type="number" x-model="payAmount" @keydown.enter="processPayment()"
                                        class="w-full pl-12 pr-6 py-4 text-right font-mono font-bold text-2xl border-4 border-gray-300 rounded-xl focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all text-gray-800 shadow-lg h-14" 
                                        placeholder="0" 
                                        autofocus>
                                    <div class="absolute -bottom-6 right-0 text-sm font-bold flex items-center gap-2"
                                         :class="changeAmount >= 0 ? 'text-green-600' : 'text-red-500'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Kembalian: <span class="text-lg" x-text="formatRupiah(changeAmount)"></span>
                                    </div>
                                </div>
                                
                                <!-- Tombol Bayar -->
                                <button @click="processPayment()" 
                                    :disabled="grandTotal <= 0 || isProcessing"
                                    class="w-full lg:w-auto bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-10 py-4 rounded-xl font-black text-xl shadow-2xl transform transition-all duration-200 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3 h-14 min-w-[180px]">
                                    <span x-show="!isProcessing" class="flex items-center gap-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        BAYAR (F10)
                                    </span>
                                    <span x-show="isProcessing" class="flex items-center gap-3">
                                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Memproses...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: RIWAYAT -->
            <div x-show="tab === 'history'" class="h-full flex flex-col p-6 bg-gradient-to-br from-gray-100 to-gray-200" style="display: none;">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-300 h-full flex flex-col overflow-hidden">
                    <!-- Header Riwayat -->
                    <div class="px-8 py-6 border-b bg-gradient-to-r from-gray-50 to-white">
                       <div class="flex justify-between items-center">
                           <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                               <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                               </svg>
                               Riwayat Transaksi
                           </h2>
                           <div class="text-sm text-gray-600">
                               Menampilkan transaksi yang sudah selesai
                           </div>
                       </div>
                       
                       <!-- Filter -->
                       <div class="flex flex-wrap gap-4 mt-6">
                            <div class="relative flex-1 min-w-[300px]">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" x-model="historyFilter.q" @input.debounce="loadHistory()" 
                                       class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm" 
                                       placeholder="Cari berdasarkan invoice, nama pelanggan...">
                            </div>
                            
                            <select x-model="historyFilter.user_id" @change="loadHistory()" 
                                    class="border-2 border-gray-300 rounded-xl text-sm px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm w-48">
                                <option value="">👨‍💼 Semua Kasir</option>
                                @foreach($cashiers as $c) 
                                    <option value="{{ $c->id }}">{{ $c->name }}</option> 
                                @endforeach
                            </select>
                            
                            <div class="flex gap-2">
                                <input type="date" x-model="historyFilter.start_date" @change="loadHistory()" 
                                       class="border-2 border-gray-300 rounded-xl text-sm px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm w-40"
                                       placeholder="Dari Tanggal">
                                <input type="date" x-model="historyFilter.end_date" @change="loadHistory()" 
                                       class="border-2 border-gray-300 rounded-xl text-sm px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm w-40"
                                       placeholder="Sampai Tanggal">
                            </div>
                       </div>
                    </div>
                    
                    <!-- Tabel Riwayat -->
                    <div class="flex-1 overflow-auto custom-scrollbar">
                        <table class="min-w-full divide-y divide-gray-200">
                           <thead class="bg-gray-50 sticky top-0 shadow-sm">
                               <tr>
                                   <th class="px-8 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Invoice</th>
                                   <th class="px-8 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Tanggal & Waktu</th>
                                   <th class="px-8 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Pelanggan</th>
                                   <th class="px-8 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">Kasir</th>
                                   <th class="px-8 py-4 text-right text-sm font-bold text-gray-700 uppercase tracking-wider">Total</th>
                                   <th class="px-8 py-4 text-center text-sm font-bold text-gray-700 uppercase tracking-wider">Aksi</th>
                               </tr>
                           </thead>
                           <tbody class="divide-y divide-gray-100 bg-white">
                               <template x-for="trx in historyData" :key="trx.id">
                                   <tr class="hover:bg-gradient-to-r hover:from-indigo-50/30 hover:to-blue-50/30 transition-all duration-200">
                                       <td class="px-8 py-4">
                                           <div class="font-mono font-bold text-indigo-700 text-base" 
                                                x-text="trx.invoice_number"></div>
                                       </td>
                                       <td class="px-8 py-4">
                                           <div class="font-medium text-gray-900" 
                                                x-text="new Date(trx.created_at).toLocaleDateString('id-ID', { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' })"></div>
                                           <div class="text-sm text-gray-500" 
                                                x-text="new Date(trx.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})"></div>
                                       </td>
                                       <td class="px-8 py-4">
                                        <!-- Nama Pelanggan -->
                                        <div class="font-medium text-gray-900">
                                            <template x-if="trx.customer">
                                                <span x-text="trx.customer.name"></span>
                                            </template>
                                            <template x-if="!trx.customer && trx.buyer">
                                                <span x-text="trx.buyer.name + ' (Online)'"></span>
                                            </template>
                                            <template x-if="!trx.customer && !trx.buyer">
                                                <span class="text-gray-500">Pelanggan Umum</span>
                                            </template>
                                        </div>
                                        
                                        <!-- Kontak -->
                                        <div class="text-sm text-gray-500">
                                            <template x-if="trx.customer && trx.customer.phone">
                                                <span x-text="trx.customer.phone"></span>
                                            </template>
                                            <template x-if="!trx.customer && trx.buyer && trx.buyer.email">
                                                <span x-text="trx.buyer.email"></span>
                                            </template>
                                        </div>
                                    </td>
                                       <td class="px-8 py-4">
                                           <div class="font-bold text-gray-800" 
                                                x-text="trx.user ? trx.user.name : '-'"></div>
                                       </td>
                                       <td class="px-8 py-4 text-right">
                                           <div class="font-black text-lg text-gray-900" 
                                                x-text="formatRupiah(trx.total_amount)"></div>
                                       </td>
                                       <td class="px-8 py-4 text-center">
                                           <button @click="printReceipt(trx.invoice_number)" 
                                                   class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2">
                                               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                               </svg>
                                               Cetak
                                           </button>
                                       </td>
                                   </tr>
                               </template>
                               
                               <!-- Empty State -->
                               <tr x-show="historyData.length === 0">
                                    <td colspan="6" class="px-8 py-16 text-center">
                                        <div class="text-gray-400">
                                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            <div class="text-lg font-medium text-gray-500 mb-2">Belum ada riwayat transaksi</div>
                                            <div class="text-sm text-gray-400">Transaksi yang sudah selesai akan muncul di sini</div>
                                        </div>
                                    </td>
                                </tr>
                           </tbody>
                       </table>
                    </div>
                    
                    <!-- Pagination Info -->
                    <div class="px-8 py-4 border-t bg-gray-50 text-sm text-gray-600">
                        Menampilkan <span x-text="historyData.length"></span> transaksi
                    </div>
                </div>
            </div>
            
            <!-- TAB 3: ONLINE -->
            <div x-show="tab === 'online'" class="h-full flex flex-col p-6 bg-gradient-to-br from-gray-100 to-gray-200" style="display: none;">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-300 h-full flex flex-col overflow-hidden">
                    <!-- Header Online -->
                    <div class="px-8 py-6 border-b bg-gradient-to-r from-gray-50 to-white">
                       <div class="flex justify-between items-center">
                           <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                               <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9-3-9m-9 9a9 9 0 019-9"></path>
                               </svg>
                               Pesanan Online
                           </h2>
                           <div class="text-sm text-gray-600">
                               @if($pendingCount > 0)
                                   <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full font-bold">{{ $pendingCount }} pesanan menunggu</span>
                               @else
                                   Tidak ada pesanan menunggu
                               @endif
                           </div>
                       </div>
                       
                       <!-- Pencarian dan Filter -->
                       <div class="flex gap-4 mt-6">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" x-model="onlineSearch" @keyup.enter="loadOnlineOrders()" 
                                       class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm" 
                                       placeholder="Cari invoice / pelanggan...">
                            </div>
                            <button @click="loadOnlineOrders()" 
                                    :disabled="loadingOnlineOrders"
                                    class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-xl font-bold text-sm hover:from-indigo-700 hover:to-purple-700 shadow-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg x-show="!loadingOnlineOrders" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <svg x-show="loadingOnlineOrders" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="loadingOnlineOrders ? 'Loading...' : 'Refresh'"></span>
                            </button>
                       </div>
                    </div>
                    
                    <!-- Daftar Pesanan Online -->
                    <div class="flex-1 overflow-auto p-8">
                        <template x-if="onlineOrders.length === 0">
                            <div class="text-center py-16">
                                <svg class="w-20 h-20 mx-auto mb-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9-3-9m-9 9a9 9 0 019-9"></path>
                                </svg>
                                <div class="text-xl font-medium text-gray-500 mb-2">Tidak ada pesanan online</div>
                                <div class="text-gray-400">Semua pesanan sudah diproses atau tidak ada pesanan baru</div>
                            </div>
                        </template>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                            <template x-for="order in onlineOrders" :key="order.id">
                                <div :class="{
                                    'bg-gradient-to-br from-yellow-50 to-amber-50 border-2 border-yellow-300': order.status === 'pending',
                                    'bg-gradient-to-br from-blue-50 to-cyan-50 border-2 border-blue-300': order.status === 'process',
                                    'bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-300': order.status === 'ready'
                                }" class="rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                                    
                                    <!-- Header Pesanan -->
                                    <div class="flex justify-between items-start mb-6 pb-4 border-b border-gray-200/50">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-3">
                                                <div class="font-mono font-bold text-gray-800 text-lg" 
                                                     x-text="order.invoice_number"></div>
                                                <span :class="{
                                                    'bg-gradient-to-r from-yellow-500 to-amber-500': order.status === 'pending',
                                                    'bg-gradient-to-r from-blue-500 to-cyan-500': order.status === 'process',
                                                    'bg-gradient-to-r from-green-500 to-emerald-500': order.status === 'ready'
                                                }" class="text-white text-xs px-3 py-1.5 rounded-full font-bold shadow-sm" 
                                                     x-text="order.status.toUpperCase()">
                                                </span>
                                            </div>
                                            <div class="space-y-2">
                                                <div class="text-sm text-gray-600">
                                                    <span class="font-bold">Customer:</span> 
                                                    <span class="font-medium text-gray-800 ml-2" 
                                                          x-text="order.customer ? order.customer.name : (order.buyer ? order.buyer.name : 'Guest')"></span>
                                                </div>
                                                <div class="text-sm text-gray-600 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span x-text="new Date(order.created_at).toLocaleString('id-ID')"></span>
                                                </div>
                                                <div class="text-sm text-gray-600" x-show="order.details">
                                                    <span class="font-bold">Items:</span> 
                                                    <span class="ml-2" x-text="order.details.length"></span> barang
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-black text-2xl text-gray-900 mb-1" 
                                                 x-text="formatRupiah(order.total_amount)"></div>
                                            <div class="text-xs text-gray-500">Total Pembayaran</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Tombol Aksi -->
                                    <div class="space-y-3">
                                        <template x-if="order.status === 'pending'">
                                            <div class="grid grid-cols-3 gap-3">
                                                <button @click="updateOrderStatus(order.id, 'process')" 
                                                        class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-3 rounded-lg font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-200">
                                                    🚀 Proses
                                                </button>
                                                <button @click="viewOnlineOrder(order.id)" 
                                                        class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-4 py-3 rounded-lg font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-200">
                                                    💳 Bayar
                                                </button>
                                                <button @click="rejectOrder(order.id)" 
                                                        class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-4 py-3 rounded-lg font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-200">
                                                    ❌ Batal
                                                </button>
                                            </div>
                                        </template>
                                        
                                        <template x-if="order.status === 'process'">
                                            <div class="grid grid-cols-3 gap-3">
                                                <button @click="updateOrderStatus(order.id, 'pending')" 
                                                        class="bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-600 hover:to-amber-600 text-white px-4 py-3 rounded-lg font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-200">
                                                    ↩ Kembali
                                                </button>
                                                <button @click="updateOrderStatus(order.id, 'ready')" 
                                                        class="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white px-4 py-3 rounded-lg font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-200">
                                                    ✅ Siap
                                                </button>
                                                <button @click="viewOnlineOrder(order.id)" 
                                                        class="bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white px-4 py-3 rounded-lg font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-200">
                                                    💰 Bayar
                                                </button>
                                            </div>
                                        </template>
                                        
                                        <template x-if="order.status === 'ready'">
                                            <div class="grid grid-cols-1 gap-3">
                                                <button @click="viewOnlineOrder(order.id)" 
                                                        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-6 py-4 rounded-lg font-bold shadow-lg hover:shadow-xl transition-all duration-200 text-base">
                                                    🎉 Bayar & Selesaikan Pesanan
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL PESANAN ONLINE -->
    <div x-show="showOnlineDetailModal" 
         class="fixed inset-0 z-[1000] flex items-center justify-center bg-black/70 backdrop-blur-sm" 
         x-cloak
         style="display: none;">
        
        <div class="bg-white rounded-2xl shadow-2xl w-[600px] max-h-[85vh] flex flex-col animate-fadeIn" 
             @click.away="showOnlineDetailModal = false">
            <!-- Header Modal -->
            <div class="px-8 py-6 border-b bg-gradient-to-r from-indigo-600 to-purple-600 rounded-t-2xl">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Detail Pesanan Online
                    </h3>
                    <button @click="showOnlineDetailModal = false" 
                            :disabled="isProcessingOnline"
                            class="text-white hover:text-gray-200 font-bold text-2xl disabled:opacity-50 transition">
                        ✕
                    </button>
                </div>
            </div>
            
            <!-- Body Modal -->
            <div class="p-8 overflow-y-auto flex-1 bg-gradient-to-b from-gray-50 to-white modal-scrollbar">
                <template x-if="onlineOrder">
                    <div class="space-y-8">
                        <!-- Info Customer -->
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-lg">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Info Pelanggan</div>
                                    <div class="font-bold text-xl text-gray-800 mb-1">
                                        <template x-if="onlineOrder.customer">
                                            <span x-text="onlineOrder.customer.name"></span>
                                        </template>
                                        <template x-if="!onlineOrder.customer && onlineOrder.buyer">
                                            <span x-text="onlineOrder.buyer.name"></span>
                                        </template>
                                        <template x-if="!onlineOrder.customer && !onlineOrder.buyer">
                                            <span>Guest</span>
                                        </template>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Waktu Order: <span x-text="new Date(onlineOrder.created_at).toLocaleString('id-ID')"></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Invoice</div>
                                    <div class="font-mono font-bold text-2xl text-indigo-600 mb-2" 
                                         x-text="onlineOrder.invoice_number"></div>
                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                        STATUS: <span x-text="onlineOrder.status.toUpperCase()" class="ml-1"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Produk -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b">
                                <div class="font-bold text-gray-800">Daftar Barang</div>
                            </div>
                            <div class="divide-y divide-gray-100">
                                <template x-for="d in onlineOrder.details" :key="d.id">
                                    <div class="px-6 py-4 hover:bg-gray-50/50 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <div class="flex-1">
                                                <div class="font-bold text-gray-800 text-lg" 
                                                     x-text="d.product_name || 'Produk'"></div>
                                                <div class="text-sm text-gray-500 mt-1">
                                                    <span x-text="d.quantity"></span> x 
                                                    <span x-text="d.product_unit?.unit?.name || 'Satuan'"></span> 
                                                    @ <span x-text="formatRupiah(d.price_at_purchase)"></span>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-bold text-xl text-indigo-600" 
                                                     x-text="formatRupiah(d.subtotal)"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Total & Pembayaran -->
                        <div class="space-y-6">
                            <!-- Total -->
                            <div class="flex justify-between items-center p-6 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl border border-indigo-100">
                                <span class="font-bold text-2xl text-gray-800">TOTAL TAGIHAN</span>
                                <span class="font-black text-3xl text-indigo-700" 
                                      x-text="formatRupiah(onlineOrder.total_amount)">
                                </span>
                            </div>

                            <!-- Input Pembayaran -->
                            <div class="bg-gradient-to-r from-amber-50 to-yellow-50 p-6 rounded-xl border-2 border-amber-200 shadow-lg">
                                <div class="mb-6">
                                    <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">
                                        💵 Uang Diterima (Rp)
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-gray-500 font-bold text-xl">Rp</span>
                                        </div>
                                        <input type="number" 
                                               x-model="onlinePayAmount" 
                                               @input="onlinePayAmount = Math.max(0, onlinePayAmount)"
                                               :disabled="isProcessingOnline"
                                               class="w-full pl-14 pr-6 py-4 text-right font-mono font-bold text-2xl border-3 border-gray-300 rounded-lg focus:ring-4 focus:ring-green-100 focus:border-green-500 disabled:bg-gray-100 transition-all" 
                                               placeholder="0"
                                               min="0"
                                               step="500">
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-gray-600 mb-2">Kembalian</div>
                                    <div class="font-mono font-black text-3xl" 
                                         :class="onlineChangeAmount >= 0 ? 'text-green-600' : 'text-red-500'" 
                                         x-text="formatRupiah(onlineChangeAmount)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer Actions -->
            <div class="px-8 py-6 border-t bg-white rounded-b-2xl flex justify-between items-center shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
                <button @click="showOnlineDetailModal = false" 
                        :disabled="isProcessingOnline"
                        class="px-6 py-3 bg-gray-100 text-gray-600 rounded-lg font-bold hover:bg-gray-200 disabled:opacity-50 transition-all">
                    Batal
                </button>
                
                <button @click="processOnlineOrder()" 
                        :disabled="onlineChangeAmount < 0 || isProcessingOnline"
                        :class="onlineChangeAmount < 0 ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-2xl hover:-translate-y-1'"
                        class="px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-bold shadow-xl transition-all transform flex items-center justify-center gap-3 min-w-[200px]">
                    <template x-if="!isProcessingOnline">
                        <span class="flex items-center gap-3 text-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            BAYAR & SELESAIKAN
                        </span>
                    </template>
                    <template x-if="isProcessingOnline">
                        <span class="flex items-center gap-3">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses Pembayaran...
                        </span>
                    </template>
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL CUSTOMER -->
    <div x-show="showCustomerModal" 
         class="fixed inset-0 z-[1000] flex items-center justify-center bg-black/70 backdrop-blur-sm" 
         x-cloak
         style="display: none;">
        
        <div class="bg-white rounded-2xl shadow-2xl w-[700px] max-h-[80vh] flex flex-col animate-fadeIn" 
             @click.away="showCustomerModal = false">
            
            <!-- Header Modal -->
            <div class="px-8 py-6 border-b bg-gradient-to-r from-green-600 to-emerald-600 rounded-t-2xl">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Kelola Data Member
                    </h3>
                    <button @click="showCustomerModal = false" 
                            class="text-white hover:text-gray-200 font-bold text-2xl transition">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Form Input -->
            <div class="p-8 border-b bg-gradient-to-r from-gray-50 to-white">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama Member</label>
                        <input type="text" x-model="newCustomerName" 
                               class="w-full border-2 border-gray-300 rounded-lg text-sm h-11 px-4 focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                               placeholder="Masukkan nama member..." 
                               @keydown.enter="saveCustomer()">
                    </div>
                    <div class="w-48">
                        <label class="block text-sm font-bold text-gray-700 mb-2">No. HP</label>
                        <input type="text" x-model="newCustomerPhone" 
                               @input="newCustomerPhone = newCustomerPhone.replace(/[^0-9]/g, '')"
                               class="w-full border-2 border-gray-300 rounded-lg text-sm h-11 px-4 focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                               placeholder="0812..." 
                               @keydown.enter="saveCustomer()"
                               maxlength="15">
                    </div>
                    <div class="pt-8">
                        <button @click="saveCustomer()" 
                                class="h-11 bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 rounded-lg font-bold hover:from-green-700 hover:to-emerald-700 shadow-lg transition-all">
                            + Tambah
                        </button>
                    </div>
                </div>
            </div>

            <!-- Daftar Member -->
            <div class="flex-1 overflow-auto p-6 modal-scrollbar">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Nama Member</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">No. HP</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <template x-for="cust in customersList" :key="cust.id">
                                <tr class="hover:bg-gradient-to-r hover:from-indigo-50/30 hover:to-blue-50/30 transition-colors"
                                    :class="{'bg-indigo-50/50': cust.id === editingCustId}">
                                    
                                    <!-- Tampilan Normal -->
                                    <td class="px-6 py-4" x-show="editingCustId !== cust.id" 
                                        @click="selectCustomerFromList(cust)">
                                        <div class="font-medium text-gray-900" x-text="cust.name"></div>
                                    </td>
                                    <td class="px-6 py-4" x-show="editingCustId !== cust.id" 
                                        @click="selectCustomerFromList(cust)">
                                        <div class="font-mono text-gray-600" x-text="cust.phone || '-'"></div>
                                    </td>
                                    <td class="px-6 py-4 text-center" x-show="editingCustId !== cust.id">
                                        <div class="flex justify-center gap-2">
                                            <button @click.stop="editCustomer(cust)" 
                                                    class="text-blue-600 hover:text-blue-800 font-bold text-sm px-3 py-1.5 bg-blue-50 rounded-lg hover:bg-blue-100 transition-all">
                                                Edit
                                            </button>
                                            <button @click.stop="deleteCustomer(cust.id)" 
                                                    class="text-red-600 hover:text-red-800 font-bold text-sm px-3 py-1.5 bg-red-50 rounded-lg hover:bg-red-100 transition-all">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>

                                    <!-- Tampilan Edit -->
                                    <td class="px-6 py-4" x-show="editingCustId === cust.id">
                                        <input type="text" x-model="editCustName" 
                                               class="w-full border-2 border-gray-300 rounded-lg text-sm h-9 px-3 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </td>
                                    <td class="px-6 py-4" x-show="editingCustId === cust.id">
                                        <input type="text" x-model="editCustPhone" 
                                               @input="editCustPhone = editCustPhone.replace(/[^0-9]/g, '')"
                                               class="w-full border-2 border-gray-300 rounded-lg text-sm h-9 px-3 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                               maxlength="15">
                                    </td>
                                    <td class="px-6 py-4 text-center" x-show="editingCustId === cust.id">
                                        <div class="flex justify-center gap-2">
                                            <button @click="updateCustomer(cust.id)" 
                                                    class="text-green-600 hover:text-green-800 font-bold text-sm px-3 py-1.5 bg-green-50 rounded-lg hover:bg-green-100 transition-all">
                                                Simpan
                                            </button>
                                            <button @click="editingCustId = null" 
                                                    class="text-gray-600 hover:text-gray-800 font-bold text-sm px-3 py-1.5 bg-gray-50 rounded-lg hover:bg-gray-100 transition-all">
                                                Batal
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            
                            <!-- Empty State -->
                            <tr x-show="customersList.length === 0">
                                <td colspan="3" class="px-6 py-16 text-center">
                                    <div class="text-gray-400">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <div class="text-lg font-medium text-gray-500 mb-2">Belum ada member</div>
                                        <div class="text-sm text-gray-400">Tambahkan member baru menggunakan form di atas</div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-8 py-4 border-t bg-gray-50 text-sm text-gray-600 rounded-b-2xl">
                Total <span x-text="customersList.length"></span> member terdaftar
            </div>
        </div>
    </div>

    <script>
    // Logout function
    function logout() {
        if (confirm('Yakin ingin logout?')) {
            document.getElementById('logout-form').submit();
        }
    }
    </script>

    @include('cashier.pos.script')
</body>
</html>

