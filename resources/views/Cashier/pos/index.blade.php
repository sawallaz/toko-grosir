@extends('layouts.cashier') 

@section('content')
    <!-- Konten utama akan menggunakan Alpine.js dari layout -->
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
        
        <!-- TAB 3: ONLINE (DENGAN MULTI STATUS) -->
<div x-show="tab === 'online'" class="h-full p-4 bg-gray-100" style="display: none;">
    <div class="bg-white rounded-xl shadow border border-gray-200 h-full overflow-auto p-6">
        <h3 class="font-bold text-xl mb-4 text-indigo-700 flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9-3-9m-9 9a9 9 0 019-9"></path>
            </svg>
            Pesanan Online
        </h3>
        
        <!-- Tabs Status -->
        <div class="flex border-b border-gray-200 mb-4">
            <button @click="onlineOrderTab = 'pending'" 
                    :class="onlineOrderTab === 'pending' ? 'border-b-2 border-yellow-500 text-yellow-600' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 font-bold text-sm flex items-center gap-2">
                <span class="w-3 h-3 bg-yellow-400 rounded-full"></span>
                Menunggu
                @if($pendingOrders->total() > 0)
                <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">{{ $pendingOrders->total() }}</span>
                @endif
            </button>
            
            <button @click="onlineOrderTab = 'process'" 
                    :class="onlineOrderTab === 'process' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 font-bold text-sm flex items-center gap-2">
                <span class="w-3 h-3 bg-blue-400 rounded-full"></span>
                Diproses
                @if($processOrders->total() > 0)
                <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">{{ $processOrders->total() }}</span>
                @endif
            </button>

            <button @click="onlineOrderTab = 'completed'" 
                    :class="onlineOrderTab === 'completed' ? 'border-b-2 border-green-500 text-green-600' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 font-bold text-sm flex items-center gap-2">
                <span class="w-3 h-3 bg-green-400 rounded-full"></span>
                Selesai
                @if($completedOrders->total() > 0)
                <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">{{ $completedOrders->total() }}</span>
                @endif
            </button>

            <button @click="onlineOrderTab = 'cancelled'" 
                    :class="onlineOrderTab === 'cancelled' ? 'border-b-2 border-red-500 text-red-600' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 font-bold text-sm flex items-center gap-2">
                <span class="w-3 h-3 bg-red-400 rounded-full"></span>
                Dibatalkan
                @if($cancelledOrders->total() > 0)
                <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $cancelledOrders->total() }}</span>
                @endif
            </button>
        </div>

        <!-- Tab Content -->
        <div class="space-y-4">
            <!-- PENDING ORDERS -->
            <template x-if="onlineOrderTab === 'pending'">
                <div>
                    @forelse($pendingOrders as $order)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-3">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-mono font-bold text-yellow-700">{{ $order->invoice_number }}</span>
                                    <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">MENUNGGU</span>
                                    @if($order->payment_method === 'rejected')
                                    <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">DITOLAK</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600 mb-1">
                                    <strong>Customer:</strong> {{ $order->buyer ? $order->buyer->name : ($order->customer ? $order->customer->name : 'Guest') }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    <strong>Waktu Order:</strong> {{ $order->created_at->format('d/m/Y H:i') }}
                                </div>
                                <div class="text-sm text-yellow-600 font-bold">
                                    ⚠️ Customer masih bisa cancel pesanan ini
                                </div>
                                <div class="text-lg font-black text-gray-800 mt-2">
                                    Rp {{ number_format($order->total_amount) }}
                                </div>
                            </div>
                            <div class="flex gap-2 flex-col">
                                <div class="flex gap-2">
                                    <button @click="updateOrderStatus({{ $order->id }}, 'process')" 
                                            class="bg-blue-600 text-white px-3 py-2 rounded text-xs font-bold hover:bg-blue-700">
                                        PROSES
                                    </button>
                                    <button @click="viewOnlineOrder({{ $order->id }})" 
                                            class="bg-green-600 text-white px-3 py-2 rounded text-xs font-bold hover:bg-green-700">
                                        BAYAR
                                    </button>
                                </div>
                                <button @click="rejectOrder({{ $order->id }})" 
                                        class="bg-red-600 text-white px-3 py-2 rounded text-xs font-bold hover:bg-red-700">
                                    TOLAK PESANAN
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-400">
                        Tidak ada pesanan menunggu
                    </div>
                    @endforelse
                    <div class="mt-4">
                        {{ $pendingOrders->links() }}
                    </div>
                </div>
            </template>

            <!-- PROCESS ORDERS -->
            <template x-if="onlineOrderTab === 'process'">
                <div>
                    @forelse($processOrders as $order)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-mono font-bold text-blue-700">{{ $order->invoice_number }}</span>
                                    <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">DIPROSES</span>
                                </div>
                                <div class="text-sm text-gray-600 mb-1">
                                    <strong>Customer:</strong> {{ $order->buyer ? $order->buyer->name : ($order->customer ? $order->customer->name : 'Guest') }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    <strong>Waktu Order:</strong> {{ $order->created_at->format('d/m/Y H:i') }}
                                </div>
                                <div class="text-sm text-green-600 font-bold">
                                    ✅ Customer TIDAK BISA cancel pesanan ini
                                </div>
                                <div class="text-lg font-black text-gray-800 mt-2">
                                    Rp {{ number_format($order->total_amount) }}
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button @click="updateOrderStatus({{ $order->id }}, 'pending')" 
                                        class="bg-yellow-600 text-white px-3 py-2 rounded text-xs font-bold hover:bg-yellow-700">
                                        KEMBALI
                                </button>
                                <button @click="updateOrderStatus({{ $order->id }}, 'completed')" 
                                        class="bg-green-600 text-white px-3 py-2 rounded text-xs font-bold hover:bg-green-700">
                                        SIAP
                                </button>
                                <button @click="viewOnlineOrder({{ $order->id }})" 
                                        class="bg-indigo-600 text-white px-3 py-2 rounded text-xs font-bold hover:bg-indigo-700">
                                        BAYAR
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-400">
                        Tidak ada pesanan diproses
                    </div>
                    @endforelse
                    <div class="mt-4">
                        {{ $processOrders->links() }}
                    </div>
                </div>
            </template>

            <!-- COMPLETED ORDERS -->
            <template x-if="onlineOrderTab === 'completed'">
                <div>
                    @forelse($completedOrders as $order)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-3">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-mono font-bold text-green-700">{{ $order->invoice_number }}</span>
                                    <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">SELESAI</span>
                                </div>
                                <div class="text-sm text-gray-600 mb-1">
                                    <strong>Customer:</strong> {{ $order->buyer ? $order->buyer->name : ($order->customer ? $order->customer->name : 'Guest') }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    <strong>Kasir:</strong> {{ $order->user ? $order->user->name : '-' }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    <strong>Waktu Selesai:</strong> {{ $order->updated_at->format('d/m/Y H:i') }}
                                </div>
                                <div class="text-lg font-black text-gray-800 mt-2">
                                    Rp {{ number_format($order->total_amount) }}
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button @click="printReceipt('{{ $order->invoice_number }}')" 
                                        class="bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-blue-700">
                                    CETAK
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-400">
                        Tidak ada pesanan selesai
                    </div>
                    @endforelse
                    <div class="mt-4">
                        {{ $completedOrders->links() }}
                    </div>
                </div>
            </template>

            <!-- CANCELLED ORDERS -->
            <template x-if="onlineOrderTab === 'cancelled'">
                <div>
                    @forelse($cancelledOrders as $order)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-3">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-mono font-bold text-red-700">{{ $order->invoice_number }}</span>
                                    <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">DIBATALKAN</span>
                                    @if($order->payment_method === 'rejected')
                                    <span class="bg-orange-500 text-white text-xs px-2 py-1 rounded-full">DITOLAK KASIR</span>
                                    @else
                                    <span class="bg-purple-500 text-white text-xs px-2 py-1 rounded-full">DIBATALKAN CUSTOMER</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600 mb-1">
                                    <strong>Customer:</strong> {{ $order->buyer ? $order->buyer->name : ($order->customer ? $order->customer->name : 'Guest') }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    <strong>Kasir:</strong> {{ $order->user ? $order->user->name : 'Customer' }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    <strong>Waktu Batal:</strong> {{ $order->updated_at->format('d/m/Y H:i') }}
                                </div>
                                <div class="text-lg font-black text-gray-800 mt-2">
                                    Rp {{ number_format($order->total_amount) }}
                                </div>
                                <div class="text-sm text-red-600 font-bold mt-1">
                                    @if($order->payment_method === 'rejected')
                                    ❌ Pesanan ditolak oleh kasir
                                    @else
                                    ❌ Pesanan dibatalkan oleh customer
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-400">
                        Tidak ada pesanan dibatalkan
                    </div>
                    @endforelse
                    <div class="mt-4">
                        {{ $cancelledOrders->links() }}
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

    <!-- [PERBAIKAN] MODAL DETAIL PESANAN ONLINE -->
<div x-show="showOnlineDetailModal" 
     class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm" 
     x-cloak
     style="display: none;">
    
    <div class="bg-white rounded-xl shadow-2xl w-[600px] max-h-[90vh] flex flex-col transform transition-all" 
         @click.away="showOnlineDetailModal = false">
        <!-- Header Modal -->
        <div class="p-5 border-b flex justify-between items-center bg-indigo-600 rounded-t-xl">
            <h3 class="font-bold text-lg text-white flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Detail Pesanan Online
            </h3>
            <button @click="showOnlineDetailModal = false" 
                    :disabled="isProcessingOnline"
                    class="text-white hover:text-gray-200 font-bold text-xl disabled:opacity-50">
                ✕
            </button>
        </div>
        
        <!-- Body Modal -->
        <div class="p-6 overflow-y-auto flex-1 bg-gray-50">
            <template x-if="onlineOrder">
                <div class="space-y-6">
                    
                    <!-- Info Customer -->
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm flex justify-between items-start">
                        <div>
                            <div class="text-xs text-gray-500 uppercase font-bold mb-1">Pelanggan</div>
                            <div class="font-bold text-gray-800 text-lg" 
                                 x-text="onlineOrder.buyer ? onlineOrder.buyer.name : (onlineOrder.customer ? onlineOrder.customer.name : 'Guest')">
                            </div>
                            <div class="text-sm text-gray-500" x-text="new Date(onlineOrder.created_at).toLocaleString('id-ID')"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500 uppercase font-bold mb-1">Invoice</div>
                            <div class="font-mono font-black text-indigo-600 text-lg" x-text="onlineOrder.invoice_number"></div>
                            <div class="text-xs text-orange-600 font-bold mt-1">STATUS: PENDING</div>
                        </div>
                    </div>

                    <!-- Tabel Barang -->
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 text-gray-600 font-bold border-b">
                                <tr>
                                    <th class="p-3 text-left">Barang</th>
                                    <th class="p-3 text-center">Qty</th>
                                    <th class="p-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <template x-for="d in onlineOrder.details" :key="d.id">
                                    <tr>
                                        <td class="p-3">
                                            <div class="font-bold text-gray-800" x-text="d.product_unit.product.name"></div>
                                            <div class="text-xs text-gray-500" 
                                                 x-text="`${d.product_unit.unit.name} @ ${formatRupiah(d.price_at_purchase)}`">
                                            </div>
                                        </td>
                                        <td class="p-3 text-center font-bold" x-text="d.quantity"></td>
                                        <td class="p-3 text-right font-bold text-indigo-600" 
                                            x-text="formatRupiah(d.subtotal)">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Total & Pembayaran -->
                    <div class="border-t-2 border-dashed border-gray-300 pt-4">
                        <div class="flex justify-between items-center mb-4">
                            <span class="font-bold text-xl text-gray-700">TOTAL TAGIHAN</span>
                            <span class="font-black text-3xl text-indigo-700" 
                                  x-text="formatRupiah(onlineOrder.total_amount)">
                            </span>
                        </div>

                        <!-- Input Pembayaran -->
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                            <div class="flex items-center gap-4">
                                <div class="flex-1">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">
                                        Uang Diterima (Rp)
                                    </label>
                                    <input type="number" 
                                           x-model="onlinePayAmount" 
                                           :disabled="isProcessingOnline"
                                           class="w-full border-2 border-gray-300 rounded-lg text-right font-mono font-bold text-xl focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100" 
                                           placeholder="0"
                                           min="0"
                                           step="500">
                                </div>
                                <div class="flex-1 text-right">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kembalian</label>
                                    <div class="font-mono font-black text-xl" 
                                         :class="onlineChangeAmount >= 0 ? 'text-green-600' : 'text-red-500'" 
                                         x-text="formatRupiah(onlineChangeAmount)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer Actions -->
        <div class="p-5 border-t bg-white rounded-b-xl flex justify-end gap-3 shadow-[0_-4px_10px_rgba(0,0,0,0.05)] z-10">
            <button @click="showOnlineDetailModal = false" 
                    :disabled="isProcessingOnline"
                    class="px-5 py-3 bg-gray-100 text-gray-600 rounded-lg font-bold hover:bg-gray-200 disabled:opacity-50">
                Batal
            </button>
            
            <button @click="processOnlineOrder()" 
                    :disabled="onlineChangeAmount < 0 || isProcessingOnline"
                    :class="onlineChangeAmount < 0 ? 'opacity-50 cursor-not-allowed' : 'hover:scale-105'"
                    class="px-6 py-3 bg-green-600 text-white rounded-lg font-bold shadow-lg transition transform flex items-center justify-center gap-2 min-w-[160px]">
                <template x-if="!isProcessingOnline">
                    <span>BAYAR & SELESAIKAN</span>
                </template>
                <template x-if="isProcessingOnline">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </template>
            </button>
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