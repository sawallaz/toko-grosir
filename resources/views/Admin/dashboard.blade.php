@extends('layouts.admin')

{{-- Bagian Header Halaman --}}
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Dashboard Admin') }}
    </h2>
@endsection

{{-- Bagian Konten Halaman --}}
@section('content')
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <h3 class="text-lg font-medium">Selamat Datang, {{ Auth::user()->name }}!</h3>
            <p class="mt-2 text-gray-600">Anda telah berhasil masuk ke Panel Admin Toko Grosir Fadli Fajar.</p>
        </div>
    </div>

    <!-- Tambahkan widget atau ringkasan di sini nanti -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h4 class="text-gray-500">Penjualan Hari Ini</h4>
            <p class="text-3xl font-bold text-gray-900 mt-2">Rp 0</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h4 class="text-gray-500">Produk Terjual</h4>
            <p class="text-3xl font-bold text-gray-900 mt-2">0</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h4 class="text-gray-500">Total Produk</h4>
            <p class="text-3xl font-bold text-gray-900 mt-2">0</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h4 class="text-gray-500">Pelanggan Baru</h4>
            <p class="text-3xl font-bold text-gray-900 mt-2">0</p>
        </div>
    </div>
@endsection