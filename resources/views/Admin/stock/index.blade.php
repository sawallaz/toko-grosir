@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Manajemen Stok & Pembelian') }}
    </h2>
@endsection

@section('content')
<div class="container mx-auto space-y-8" x-data="stockPageManager()">

    <!-- Notifikasi -->
    @if (session('success'))
        <div class="p-4 bg-green-100 text-green-700 rounded-lg border border-green-200 flex items-center shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="p-4 bg-red-100 text-red-700 rounded-lg border border-red-200 flex items-center shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- BAGIAN 1: FORM INPUT STOK -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
        <div class="px-6 py-4 bg-indigo-600 border-b border-gray-200 flex justify-between items-center">
            <!-- Judul Berubah saat Mode Edit -->
            <h3 class="text-lg font-bold text-white flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span x-text="isEditMode ? 'Edit Data Stok Masuk' : 'Input Pembelian Stok (Barang Masuk)'"></span>
            </h3>
            <span class="text-xs font-mono text-indigo-200 bg-indigo-700 px-2 py-1 rounded">{{ date('d M Y') }}</span>
        </div>
        <div class="p-6">
            <!-- Include Form (Yang ada di form-input-stok.blade.php sudah oke, pastikan pake formAction) -->
            <!-- Update: Saya akan inject form input di sini langsung atau via include, 
                 TAPI karena Anda pakai include, pastikan file form-input-stok.blade.php Anda
                 menggunakan :action="formAction" seperti di update sebelumnya -->
            @include('admin.stock.form-input-stok')
        </div>
    </div>

    <!-- BAGIAN 2: RIWAYAT STOK -->
    <div class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Riwayat Stok Masuk Terakhir</h3>
             <!-- FILTER -->
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
            <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
                <form method="GET" action="{{ route('admin.stok.index') }}" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <div class="flex items-center gap-2 bg-white p-1 rounded border border-gray-300">
                        <input type="date" name="start_date" class="text-sm border-none focus:ring-0 p-1 text-gray-600" value="{{ request('start_date') }}">
                        <span class="text-gray-400">-</span>
                        <input type="date" name="end_date" class="text-sm border-none focus:ring-0 p-1 text-gray-600" value="{{ request('end_date') }}">
                    </div>
                    <div class="flex gap-2">
                        <input type="text" name="search_history" class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 w-full sm:w-48" placeholder="Cari Transaksi / Produk" value="{{ request('search_history') }}">
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700">Filter</button>
                        @if(request()->has('search_history'))
                            <a href="{{ route('admin.stok.index') }}" class="px-3 py-2 bg-gray-200 text-gray-600 text-sm font-medium rounded-md hover:bg-gray-300">✕</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-white">
                    <tr>
                        <th class="w-10 px-6 py-3"></th> <!-- Kolom Panah Accordion -->
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tanggal & No. Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Dibuat Oleh</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Total Nilai</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($stockHistory as $entry)
                        <!-- HEADER ROW (Klik untuk Expand) -->
                        <tr class="hover:bg-gray-50 transition cursor-pointer" @click="toggleRow({{ $entry->id }})">
                            <td class="px-6 py-4 text-center text-gray-500">
                                <!-- Ikon Panah Berputar -->
                                <svg class="w-5 h-5 transform transition-transform duration-200" 
                                     :class="isExpanded({{ $entry->id }}) ? 'rotate-90 text-indigo-600' : ''" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap align-top">
                                <div class="text-sm font-bold text-indigo-600 font-mono">{{ $entry->transaction_number }}</div>
                                <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($entry->entry_date)->format('d F Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 align-top">
                                {{ $entry->supplier->name ?? 'NB' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 align-top">
                                {{ $entry->user->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900 align-top">
                                Rp {{ number_format($entry->total_value, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <!-- Tombol Edit -->
                                <button @click.stop="editStockEntry({{ $entry->id }})" class="text-orange-600 hover:text-orange-900 font-bold text-xs border border-orange-200 bg-orange-50 px-3 py-1 rounded hover:bg-orange-100 transition">
                                    EDIT
                                </button>
                            </td>
                        </tr>
                        
                        <!-- DETAIL ROW (Accordion) -->
                        <tr x-show="isExpanded({{ $entry->id }})" class="bg-gray-50 border-b border-gray-200" style="display: none;" x-transition>
                            <td colspan="6" class="px-6 py-4">
                                <div class="pl-10 pr-4 bg-white rounded-md border border-gray-200 p-4 shadow-sm">
                                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-2 border-b pb-2">Detail Barang Masuk:</h4>
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="text-gray-500">
                                                <th class="text-left pb-2 font-medium">Produk</th>
                                                <th class="text-right pb-2 font-medium">Qty</th>
                                                <th class="text-right pb-2 font-medium">Harga Beli</th>
                                                <th class="text-right pb-2 font-medium">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($entry->details as $detail)
                                                <tr class="border-b border-dashed border-gray-100 last:border-0">
                                                    <td class="py-2 text-gray-800 font-medium">{{ $detail->productUnit->product->name ?? 'Produk Hapus' }}</td>
                                                    <td class="py-2 text-right text-gray-600">{{ $detail->quantity }} {{ $detail->productUnit->unit->short_name ?? '' }}</td>
                                                    <td class="py-2 text-right text-gray-600">Rp {{ number_format($detail->price_at_entry, 0, ',', '.') }}</td>
                                                    <td class="py-2 text-right font-bold text-gray-800">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @if($entry->notes)
                                        <div class="mt-3 text-xs text-gray-500 italic border-t pt-2">
                                            <strong>Catatan:</strong> {{ $entry->notes }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <p class="text-sm">Belum ada riwayat stok.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $stockHistory->links() }}
        </div>
    </div>

    <!-- Modal Tambah Supplier Cepat (Ada Input No HP) -->
    <div x-show="showSupplierModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
        <div class="bg-white p-6 rounded-lg shadow-xl w-96 transform transition-all" @click.away="showSupplierModal = false">
            <h3 class="text-lg font-bold mb-4 text-gray-800">Tambah Supplier Baru</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Nama Supplier</label>
                    <input type="text" x-model="newSupplierName" class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nama PT / Toko" @keydown.enter.prevent="saveSupplier()">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">No. HP / Telp (Opsional)</label>
                    <!-- [PERBAIKAN] Input No HP Kembali -->
                    <input type="text" x-model="newSupplierPhone" class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="08xxxx" @keydown.enter.prevent="saveSupplier()">
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button @click="showSupplierModal = false" class="px-4 py-2 text-gray-600 bg-gray-100 rounded text-sm hover:bg-gray-200">Batal</button>
                <button @click="saveSupplier()" class="px-4 py-2 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700">Simpan</button>
            </div>
        </div>
    </div>

</div>
@include('admin.stock.script')
@endsection