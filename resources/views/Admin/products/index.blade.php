@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Manajemen Produk') }}
    </h2>
@endsection

@section('content')
<div class="container mx-auto" x-data="productFormManager({{ json_encode($products->pluck('status', 'id')->toArray()) }})">

    <!-- Tombol Aksi & Pencarian -->
    <div class="mb-4 flex flex-col sm:flex-row justify-between items-center gap-4">
        <button @click="openCreateModal()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Produk Baru
        </button>

        <form method="GET" action="{{ route('admin.products.index') }}" class="w-full sm:w-auto">
            <div class="flex">
                <x-text-input type="text" name="search" class="block w-full sm:w-64" 
                              placeholder="Cari (Kode / Nama)..." 
                              :value="request('search')" />
                <x-primary-button class="ml-2">
                    {{ __('Cari') }}
                </x-primary-button>
            </div>
        </form>
    </div>

    <!-- Pesan Sukses/Error -->
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Tabel Data Produk -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok</th>
                        <!-- [BARU] Kolom Harga Beli Modal -->
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hrg Beli (Modal)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hrg Jual (Dasar)</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $product->kode_produk ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div class="h-10 w-10 rounded bg-gray-100 flex-shrink-0 overflow-hidden">
                                    @if($product->foto_produk)
                                        <img src="{{ Storage::url($product->foto_produk) }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.001M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold {{ $product->stock_in_base_unit <= 0 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $product->stock_in_base_unit }} 
                                <span class="text-xs font-normal text-gray-500 ml-1">{{ $product->baseUnit->unit->short_name ?? '' }}</span>
                            </td>
                            <!-- [BARU] Data Harga Beli Modal -->
                            <td class="px-6 py-4 text-sm text-gray-600">
                                Rp {{ number_format($product->baseUnit->harga_beli_modal ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                Rp {{ number_format($product->baseUnit->price ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button 
                                    @click="toggleStatus({{ $product->id }})"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    :class="statusCache['{{ $product->id }}'] === 'active' ? 'bg-green-500' : 'bg-gray-200'"
                                    role="switch" 
                                >
                                    <span 
                                        aria-hidden="true" 
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="statusCache['{{ $product->id }}'] === 'active' ? 'translate-x-5' : 'translate-x-0'"
                                    ></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-center">
                                <div class="flex justify-center space-x-2">
                                    <button @click.prevent="openEditModal({{ $product->id }})" class="p-1 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button @click.prevent="openDeleteModal({{ $product->id }}, '{{ addslashes($product->name) }}')" class="p-1 text-red-600 hover:text-red-900 hover:bg-red-50 rounded" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    <h3 class="text-lg font-medium text-gray-900">Belum ada produk</h3>
                                    <button @click="openCreateModal()" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                        + Tambah Produk Sekarang
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $products->links() }}
        </div>
    </div>
    
    @include('admin.products.create-modal')
    @include('admin.products.edit-modal')
    @include('admin.products.category-modal')
    @include('admin.products.unit-modal')
    @include('admin.products.delete-modal')

</div>

@include('admin.products.script')
@endsection