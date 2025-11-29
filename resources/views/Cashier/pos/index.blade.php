@extends('layouts.cashier') 

@section('content')
<div class="flex h-screen w-full bg-gray-100 font-sans overflow-hidden" x-data="posSystem()">
    
    <!-- A. SIDEBAR KIRI -->
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
                <button @click="logout()" class="text-indigo-300 hover:text-white transition text-xs" title="Logout">
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
                @if($onlineOrders->total() > 0) 
                    <span class="absolute right-3 bg-red-500 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full shadow-sm animate-pulse">{{ $onlineOrders->total() }}</span> 
                @endif
            </button>
        </div>
    </div>

    <!-- B. KONTEN UTAMA (KANAN) -->
    <div class="flex-1 flex flex-col h-full relative min-w-0 bg-gray-100 overflow-hidden">
        
        <!-- TAB 1: SALES -->
        <div x-show="tab === 'sales'" class="h-full flex flex-col" style="display: none;">
            
            <!-- Tabel Input -->
            <div class="flex-1 overflow-hidden flex flex-col">
                <div class="flex-1 overflow-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-800 text-white sticky top-0 z-10 shadow-md">
                            <tr>
                                <th class="px-4 py-3 text-left w-10 text-xs font-bold uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase">Scan Barcode / Nama Produk</th>
                                <th class="px-4 py-3 text-left w-32 text-xs font-bold uppercase">Satuan</th>
                                <th class="px-4 py-3 text-center w-24 text-xs font-bold uppercase">Qty</th>
                                <th class="px-4 py-3 text-right w-40 text-xs font-bold uppercase">Harga</th>
                                <th class="px-4 py-3 text-right w-48 text-xs font-bold uppercase">Subtotal</th>
                                <th class="px-4 py-3 text-center w-16 text-xs font-bold uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <template x-for="(row, index) in cart" :key="row.tempId">
                                <tr :class="{'bg-red-50': row.error, 'bg-indigo-50': row.active}" class="group transition-colors">
                                    <td class="px-4 py-2 text-center text-gray-500 text-sm" x-text="index + 1"></td>
                                    
                                    <!-- Search Input -->
                                    <td class="px-4 py-2 relative">
                                        <input type="text" 
                                               x-model="row.product_name" 
                                               @input.debounce.300ms="searchProduct(index)" 
                                               @keydown.down.prevent="focusNextResult(index)"
                                               @keydown.up.prevent="focusPrevResult(index)"
                                               @keydown.enter.prevent="selectResult(index)"
                                               @keydown.right.prevent="focusNext($event)"
                                               @focus="row.active = true" @blur="setTimeout(() => row.active = false, 200)"
                                               class="w-full border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-bold text-gray-800 placeholder-gray-400 uppercase shadow-sm h-9" 
                                               placeholder="Scan / Ketik..." 
                                               autocomplete="off">
                                        
                                        <!-- Dropdown Result -->
                                        <div x-show="row.showResults && row.results.length > 0" 
                                             class="absolute z-[999] w-[150%] bg-white border border-gray-300 shadow-2xl rounded-lg mt-1 max-h-64 overflow-y-auto left-0">
                                            <ul>
                                                <template x-for="(res, resIdx) in row.results" :key="res.id">
                                                    <li @click="selectProductManual(index, res)" 
                                                        class="px-4 py-3 text-sm cursor-pointer hover:bg-indigo-50 border-b flex justify-between items-center group/item"
                                                        :class="{'bg-indigo-100': row.focusIndex === resIdx}">
                                                        <div>
                                                            <div class="font-bold text-gray-800" x-text="res.name"></div>
                                                            <div class="text-xs text-gray-500 font-mono" x-text="res.kode_produk"></div>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="text-[10px] px-2 py-0.5 rounded font-bold border" :class="res.stock > 0 ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'">
                                                                Stok: <span x-text="res.stock"></span>
                                                            </span>
                                                            <div class="font-bold text-indigo-600 text-xs mt-1" x-text="formatRupiah(res.units[0].price)"></div>
                                                        </div>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </td>

                                    <!-- Satuan -->
                                    <td class="px-4 py-2">
                                        <select x-model="row.product_unit_id" @change="updateUnit(index)" 
                                                class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white shadow-sm h-9"
                                                :disabled="!row.product_id">
                                            <template x-for="u in row.available_units" :key="u.product_unit_id">
                                                <option :value="u.product_unit_id" x-text="u.unit_short_name || u.unit_name"></option>
                                            </template>
                                        </select>
                                        <div x-show="row.isWholesale" class="text-[9px] text-green-600 font-bold italic mt-1 text-center">Grosir Aktif!</div>
                                    </td>

                                    <!-- Qty -->
                                    <td class="px-4 py-2">
                                        <input type="number" x-model="row.qty" min="1" @input="updateSubtotal(index)"
                                               class="w-full text-center border-gray-300 rounded-md text-sm font-bold focus:ring-indigo-500 focus:border-indigo-500 shadow-sm h-9"
                                               :disabled="!row.product_id" placeholder="1">
                                    </td>

                                    <!-- Harga -->
                                    <td class="px-4 py-2 text-right align-middle">
                                        <span class="font-mono text-sm text-gray-600" x-text="formatRupiah(row.price)"></span>
                                    </td>

                                    <!-- Subtotal -->
                                    <td class="px-4 py-2 text-right align-middle font-black text-indigo-700 text-base" x-text="formatRupiah(row.subtotal)"></td>

                                    <!-- Hapus -->
                                    <td class="px-4 py-2 text-center align-middle">
                                        <button @click="removeRow(index)" class="text-gray-400 hover:text-red-600 p-1.5 rounded hover:bg-red-50 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            
                            <!-- Tombol Tambah Manual -->
                            <tr>
                                <td colspan="7" class="p-2 text-center bg-gray-50 border-t border-gray-200">
                                    <button @click="addRow()" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold w-full py-3 border-2 border-dashed border-indigo-200 rounded-lg hover:border-indigo-400 hover:bg-indigo-50 transition shadow-sm">
                                        + Baris Baru (Enter di Pencarian)
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer Pembayaran -->
            <div class="bg-white border-t border-gray-300 shadow-lg z-20 flex-shrink-0">
                <div class="p-4">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="text-left">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Tagihan</div>
                            <div class="text-3xl md:text-4xl font-black text-gray-900 tracking-tighter leading-none" x-text="formatRupiah(grandTotal)"></div>
                        </div>
                        <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
                            <div class="relative w-full md:w-56">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 font-bold">Rp</div>
                                <input type="number" x-model="payAmount" @keydown.enter="processPayment()"
                                       class="w-full pl-10 pr-4 py-2 text-right font-mono font-bold text-xl border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-100 focus:border-green-500 transition-all text-gray-800 shadow-sm h-12" 
                                       placeholder="0">
                                <div class="absolute -bottom-5 right-0 text-sm font-bold" :class="changeAmount >= 0 ? 'text-green-600' : 'text-red-500'">
                                    Kembali: <span x-text="formatRupiah(changeAmount)"></span>
                                </div>
                            </div>
                            <button @click="processPayment()" 
                                :disabled="grandTotal <= 0 || isProcessing"
                                class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-black text-xl shadow-lg transform transition active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 h-[50px]">
                            <span x-show="!isProcessing">BAYAR</span>
                            <span x-show="isProcessing" class="text-sm flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
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
        <div x-show="tab === 'history'" class="h-full flex flex-col p-4 bg-gray-100" style="display: none;">
             <div class="bg-white rounded-lg shadow border border-gray-200 h-full flex flex-col overflow-hidden">
                 <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Riwayat Transaksi</h3>
                    
                    <!-- Filter -->
                    <div class="flex gap-2">
                         <input type="text" x-model="historyFilter.q" @input.debounce="loadHistory()" class="text-sm border-gray-300 rounded h-9 w-60 px-3" placeholder="Cari Invoice / Pelanggan...">
                         <select x-model="historyFilter.user_id" @change="loadHistory()" class="text-sm border-gray-300 rounded h-9 w-40 px-3">
                             <option value="">Semua Kasir</option>
                             @foreach($cashiers as $c) <option value="{{ $c->id }}">{{ $c->name }}</option> @endforeach
                         </select>
                         <input type="date" x-model="historyFilter.start_date" @change="loadHistory()" class="text-sm border-gray-300 rounded h-9 px-3" placeholder="Dari Tanggal">
                         <input type="date" x-model="historyFilter.end_date" @change="loadHistory()" class="text-sm border-gray-300 rounded h-9 px-3" placeholder="Sampai Tanggal">
                    </div>
                 </div>
                 
                 <div class="flex-1 overflow-auto">
                     <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-white sticky top-0 shadow-sm">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase">Invoice</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase">Tanggal & Waktu</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase">Pelanggan</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase">Kasir</th>
                                <th class="px-4 py-3 text-right font-bold text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <template x-for="trx in historyData" :key="trx.id">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 font-mono text-indigo-600 font-bold" x-text="trx.invoice_number"></td>
                                    <td class="px-4 py-3 text-gray-500">
                                        <div x-text="new Date(trx.created_at).toLocaleDateString('id-ID')"></div>
                                        <div class="text-xs text-gray-400" x-text="new Date(trx.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})"></div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700" x-text="trx.customer ? trx.customer.name : 'Umum'"></td>
                                    <td class="px-4 py-3 font-bold text-gray-800" x-text="trx.user ? trx.user.name : '-'"></td>
                                    <td class="px-4 py-3 text-right font-black text-gray-900" x-text="formatRupiah(trx.total_amount)"></td>
                                    <td class="px-4 py-3 text-center">
                                        <button @click="printReceipt(trx.invoice_number)" class="text-blue-600 hover:text-blue-800 text-xs font-bold border border-blue-200 px-2 py-1 rounded bg-blue-50 hover:bg-blue-100 transition">CETAK</button>
                                    </td>
                                </tr>
                            </template>
                             <tr x-show="historyData.length === 0"><td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada data riwayat.</td></tr>
                        </tbody>
                    </table>
                 </div>
             </div>
        </div>
        
        <!-- TAB 3: ONLINE -->
        <div x-show="tab === 'online'" class="h-full p-4 bg-gray-100" style="display: none;">
             <div class="bg-white rounded-lg shadow border border-gray-200 h-full overflow-auto">
                 <div class="p-4">
                     <h3 class="font-bold text-lg mb-4 text-indigo-700">Pesanan Online (Pending)</h3>
                     <table class="w-full text-sm text-left">
                        <thead class="bg-indigo-50 text-indigo-800 font-bold">
                            <tr>
                                <th class="p-3">Invoice</th>
                                <th class="p-3">Pelanggan</th>
                                <th class="p-3 text-right">Total</th>
                                <th class="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($onlineOrders as $o)
                            <tr>
                                <td class="p-3">{{ $o->invoice_number }}</td>
                                <td class="p-3">{{ $o->customer->name }}</td>
                                <td class="p-3 text-right font-bold">Rp {{ number_format($o->total_amount) }}</td>
                                <td class="p-3 text-center"><button class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Proses</button></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="p-6 text-center text-gray-400">Tidak ada pesanan baru.</td></tr>
                            @endforelse
                        </tbody>
                     </table>
                 </div>
             </div>
        </div>

    </div>

    <!-- MODAL CUSTOMER -->
    <div x-show="showCustomerModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
        <div class="bg-white p-6 rounded-xl shadow-2xl w-[600px] flex flex-col max-h-[80vh]" @click.away="showCustomerModal = false">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h3 class="text-lg font-bold text-gray-800">Kelola Data Member</h3>
                <button @click="showCustomerModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <!-- Form Input -->
            <div class="flex gap-2 mb-4">
                <input type="text" x-model="newCustomerName" class="flex-1 border-gray-300 rounded text-sm h-9 px-3" placeholder="Nama Member" @keydown.enter="saveCustomer()">
                <input type="text" x-model="newCustomerPhone" 
                    @input="newCustomerPhone = newCustomerPhone.replace(/[^0-9]/g, '')"
                    class="w-32 border-gray-300 rounded text-sm h-9 px-3" 
                    placeholder="No HP" 
                    @keydown.enter="saveCustomer()"
                    maxlength="15">
                <button @click="saveCustomer()" class="bg-green-600 text-white px-3 rounded font-bold text-xs hover:bg-green-700 h-9">SIMPAN</button>
            </div>

            <!-- List Member -->
            <div class="flex-1 overflow-y-auto border rounded">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-left">No HP</th>
                            <th class="px-3 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="cust in customersList" :key="cust.id">
                            <tr class="hover:bg-indigo-50 cursor-pointer" :class="{'bg-indigo-100': cust.id === editingCustId}">
                                <!-- Tampilan Normal -->
                                <td class="px-3 py-2" x-show="editingCustId !== cust.id" @click="selectCustomerFromList(cust)" x-text="cust.name"></td>
                                <td class="px-3 py-2 text-gray-500" x-show="editingCustId !== cust.id" @click="selectCustomerFromList(cust)" x-text="cust.phone"></td>
                                <td class="px-3 py-2 text-center" x-show="editingCustId !== cust.id">
                                    <button @click.stop="editCustomer(cust)" class="text-blue-600 text-xs mr-2 hover:text-blue-800">Edit</button>
                                    <button @click.stop="deleteCustomer(cust.id)" class="text-red-600 text-xs hover:text-red-800">Hapus</button>
                                </td>

                                <!-- Tampilan Edit -->
                              
                                <td class="px-3 py-2" x-show="editingCustId === cust.id">
                                    <input type="text" x-model="editCustName" class="w-full border-gray-300 rounded h-7 text-xs px-2">
                                </td>
                                <td class="px-3 py-2" x-show="editingCustId === cust.id">
                                    <input type="text" x-model="editCustPhone" 
                                        @input="editCustPhone = editCustPhone.replace(/[^0-9]/g, '')"
                                        class="w-full border-gray-300 rounded h-7 text-xs px-2"
                                        maxlength="15">
                                </td>
                                <td class="px-3 py-2 text-center" x-show="editingCustId === cust.id">
                                    <button @click="updateCustomer(cust.id)" class="text-green-600 text-xs mr-2 font-bold hover:text-green-800">OK</button>
                                    <button @click="editingCustId = null" class="text-gray-500 text-xs hover:text-gray-700">Batal</button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="customersList.length === 0"><td colspan="3" class="text-center py-4 text-gray-400">Belum ada member.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@include('cashier.pos.script')
@endsection