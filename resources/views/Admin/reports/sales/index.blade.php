@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Laporan Penjualan') }}
    </h2>
@endsection

@section('content')
<div class="container mx-auto space-y-8" x-data="reportSystem()">

    <!-- 1. FILTER & EXPORT -->
    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
        <form method="GET" action="{{ route('admin.reports.sales') }}" class="flex flex-col md:flex-row justify-between items-end gap-4">
            
            <div class="flex flex-col md:flex-row gap-4 w-full">
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Periode</label>
                    <div class="flex items-center bg-gray-50 border border-gray-300 rounded-lg overflow-hidden">
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="border-none bg-transparent text-sm text-gray-700 py-2 focus:ring-0">
                        <span class="text-gray-400 px-1">-</span>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="border-none bg-transparent text-sm text-gray-700 py-2 focus:ring-0">
                    </div>
                </div>
                <div class="flex-1">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="No. Invoice / Nama Kasir...">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="bg-indigo-800 text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:bg-indigo-900 transition shadow-md">FILTER</button>
                    @if(request()->has('search') || request()->has('start_date')) <a href="{{ route('admin.reports.sales') }}" class="bg-gray-200 text-gray-600 px-4 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-300 transition">RESET</a> @endif
                </div>
            </div>
            <div>
                <label class="text-xs font-bold text-transparent uppercase mb-1 block">.</label>
                <a href="{{ route('admin.reports.sales.export', request()->query()) }}" target="_blank" class="flex items-center gap-2 bg-green-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-green-700 transition shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> EXPORT EXCEL
                </a>
            </div>
        </form>
    </div>

    <!-- 2. KARTU STATISTIK (SAMA SEPERTI SEBELUMNYA) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-green-500">
            <div class="text-gray-500 text-xs font-bold uppercase mb-1">Total Pendapatan</div>
            <div class="text-2xl font-black text-gray-800">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-blue-500">
            <div class="text-gray-500 text-xs font-bold uppercase mb-1">Total Transaksi</div>
            <div class="text-2xl font-black text-gray-800">{{ number_format($totalTransactions) }} <span class="text-sm font-normal text-gray-400">Nota</span></div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-yellow-500">
            <div class="text-gray-500 text-xs font-bold uppercase mb-1">Rata-rata / Nota</div>
            <div class="text-2xl font-black text-gray-800">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-purple-500">
            <div class="text-gray-500 text-xs font-bold uppercase mb-1">Produk Terjual</div>
            <div class="text-2xl font-black text-gray-800">{{ number_format($totalItemsSold) }} <span class="text-sm font-normal text-gray-400">Unit</span></div>
        </div>
    </div>

    <!-- 3. TABEL RIWAYAT TRANSAKSI (ACCORDION) -->
    <div class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden flex flex-col">
        
        <!-- Header Tabel & Bulk Delete -->
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-bold text-gray-800 text-lg">Rincian Transaksi</h3>
            <div x-show="selectedItems.length > 0" style="display: none;">
                <form action="{{ route('admin.reports.sales.bulk_delete') }}" method="POST" onsubmit="return confirm('Hapus data terpilih? Stok tidak berubah.');">
                    @csrf @method('DELETE')
                    <template x-for="id in selectedItems"><input type="hidden" name="ids[]" :value="id"></template>
                    <button type="submit" class="bg-red-600 text-white text-xs px-4 py-2 rounded-lg font-bold hover:bg-red-700 flex items-center shadow-sm transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        HAPUS (<span x-text="selectedItems.length"></span>)
                    </button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-3 w-10 text-center"><input type="checkbox" @change="toggleAll()" x-model="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"></th>
                        <th class="px-6 py-3 w-10"></th> <!-- Ikon Panah -->
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase">No. Invoice</th>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase">Pelanggan</th>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase">Kasir</th>
                        <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-center font-bold text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($sales as $sale)
                        <!-- BARIS HEADER (KLIK UNTUK EXPAND) -->
                        <tr class="hover:bg-gray-50 transition cursor-pointer" 
                            :class="{'bg-indigo-50': selectedItems.includes('{{ $sale->id }}')}"
                            @click="toggleDetail('{{ $sale->id }}')">
                            
                            <td class="px-6 py-4 text-center" @click.stop>
                                <input type="checkbox" value="{{ $sale->id }}" x-model="selectedItems" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>
                            <td class="px-6 py-4 text-center text-gray-400">
                                <svg class="w-5 h-5 transform transition-transform duration-200" 
                                     :class="expanded.includes('{{ $sale->id }}') ? 'rotate-90 text-indigo-600' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-indigo-600">{{ $sale->invoice_number }}</td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $sale->created_at->format('d/m/Y') }} <span class="text-xs text-gray-400 ml-1">{{ $sale->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-700 font-medium">{{ $sale->customer->name ?? 'Umum' }}</td>
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-bold border">{{ $sale->user->name ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-gray-900">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase {{ $sale->status == 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $sale->status }}
                                </span>
                            </td>
                        </tr>
                        
                        <!-- BARIS DETAIL (ACCORDION) -->
                        <tr x-show="expanded.includes('{{ $sale->id }}')" x-transition class="bg-gray-50 border-b border-gray-200">
                            <td colspan="8" class="px-6 py-4">
                                <div class="pl-12 pr-4">
                                    <div class="flex justify-between items-center mb-2 border-b border-gray-200 pb-2">
                                        <h4 class="text-xs font-bold text-gray-500 uppercase">Detail Item Belanjaan</h4>
                                        <div class="text-xs text-gray-400">Metode: {{ strtoupper($sale->payment_method) }}</div>
                                    </div>
                                    <table class="w-full text-sm bg-white rounded border border-gray-200 overflow-hidden">
                                        <thead class="bg-gray-100 text-gray-600">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs uppercase">Produk</th>
                                                <th class="px-4 py-2 text-right text-xs uppercase">Qty</th>
                                                <th class="px-4 py-2 text-right text-xs uppercase">Harga</th>
                                                <th class="px-4 py-2 text-right text-xs uppercase">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sale->details as $detail)
                                            <tr class="border-b border-gray-50 last:border-0">
                                                <td class="px-4 py-2 font-bold text-gray-700">
                                                    {{ $detail->productUnit->product->name ?? 'Produk Hapus' }}
                                                    <div class="text-[10px] text-gray-400 font-mono">{{ $detail->productUnit->product->kode_produk ?? '-' }}</div>
                                                </td>
                                                <td class="px-4 py-2 text-right text-gray-600">
                                                    {{ $detail->quantity }} {{ $detail->productUnit->unit->name ?? '' }}
                                                </td>
                                                <td class="px-4 py-2 text-right text-gray-500">Rp {{ number_format($detail->price_at_purchase, 0, ',', '.') }}</td>
                                                <td class="px-4 py-2 text-right font-bold text-indigo-600">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50">
                                            <tr>
                                                <td colspan="3" class="px-4 py-2 text-right font-bold text-gray-600 text-xs uppercase">Total Akhir</td>
                                                <td class="px-4 py-2 text-right font-black text-gray-900">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-12 text-center text-gray-400">Tidak ada data penjualan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $sales->links() }}
        </div>
    </div>

    <!-- 4. PENDAPATAN PER KASIR (PALING BAWAH) -->
    <div class="bg-white shadow-md rounded-xl border border-gray-200 overflow-hidden">
        <div class="bg-gray-800 px-6 py-4 border-b border-gray-700">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Kinerja Kasir
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Kasir</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Jumlah Transaksi</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Total Pendapatan Disetor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($cashierStats as $stat)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-bold text-gray-800">{{ $stat->user->name ?? 'User Terhapus' }}</td>
                        <td class="px-6 py-4 text-center text-gray-600 font-medium">{{ number_format($stat->count) }} Nota</td>
                        <td class="px-6 py-4 text-right font-black text-indigo-700 text-lg">Rp {{ number_format($stat->revenue, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function reportSystem() {
        return {
            selectAll: false,
            selectedItems: [],
            expanded: [], // Array untuk menyimpan ID baris yang dibuka

            toggleAll() {
                this.selectedItems = [];
                if (this.selectAll) {
                    const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
                    checkboxes.forEach((cb) => { this.selectedItems.push(cb.value); });
                }
            },
            
            // [LOGIKA GACOR] Buka Tutup Detail
            toggleDetail(id) {
                if (this.expanded.includes(id)) {
                    // Jika sudah ada, tutup (hapus dari array)
                    this.expanded = this.expanded.filter(item => item !== id);
                } else {
                    // Jika belum ada, buka (tambah ke array)
                    this.expanded.push(id);
                }
            }
        }
    }
</script>
@endsection