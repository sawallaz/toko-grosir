@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Dashboard Overview') }}
    </h2>
@endsection

@section('content')
<!-- Load Chart.js dari CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container mx-auto px-4 py-6">
    
    <!-- 1. KARTU STATISTIK -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1: Omset -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase">Omset Hari Ini</p>
                    <p class="text-2xl font-black text-gray-800 mt-1">Rp {{ number_format($omsetHariIni, 0, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-indigo-100 rounded-full text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Card 2: Transaksi -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase">Transaksi Hari Ini</p>
                    <p class="text-2xl font-black text-gray-800 mt-1">{{ $transaksiHariIni }} <span class="text-sm font-normal text-gray-400">Nota</span></p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                </div>
            </div>
        </div>

        <!-- Card 3: Profit -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase">Est. Profit Hari Ini</p>
                    <p class="text-2xl font-black text-gray-800 mt-1">Rp {{ number_format($profitHariIni, 0, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
        </div>

        <!-- Card 4: Pelanggan -->
        <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-bold uppercase">Total Member</p>
                    <p class="text-2xl font-black text-gray-800 mt-1">{{ $totalPelanggan }}</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-full text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. GRAFIK & ANALISIS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Grafik Tren Penjualan -->
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4">Tren Penjualan (7 Hari Terakhir)</h3>
            <canvas id="salesChart" height="100"></canvas>
        </div>

        <!-- Grafik Pie (Online vs Offline) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4">Komposisi Penjualan</h3>
            <div class="h-64 flex items-center justify-center">
                <canvas id="pieChart"></canvas>
            </div>
            <div class="mt-4 flex justify-center gap-4 text-sm">
                <div class="flex items-center gap-1"><span class="w-3 h-3 bg-indigo-600 rounded-full"></span> POS</div>
                <div class="flex items-center gap-1"><span class="w-3 h-3 bg-purple-500 rounded-full"></span> Online</div>
            </div>
        </div>
    </div>

    <!-- 3. TABEL DARURAT & TERBARU -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Peringatan Stok Menipis -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 bg-red-50 border-b border-red-100 flex justify-between items-center">
                <h3 class="font-bold text-red-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Stok Menipis (< 10)
                </h3>
                <a href="{{ route('admin.stok.index') }}" class="text-xs text-red-600 hover:underline font-bold">Restock ></a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">Produk</th>
                            <th class="px-4 py-2 text-center">Sisa</th>
                            <th class="px-4 py-2 text-center">Satuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($lowStockProducts as $p)
                        <tr>
                            <td class="px-4 py-2 font-medium text-gray-800">{{ $p->name }}</td>
                            <td class="px-4 py-2 text-center font-bold text-red-600">{{ $p->stock_in_base_unit }}</td>
                            <td class="px-4 py-2 text-center text-gray-500">{{ $p->baseUnit->unit->short_name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">Stok aman semua!</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transaksi Terakhir -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 bg-gray-50 border-b border-gray-200">
                <h3 class="font-bold text-gray-800">Transaksi Terakhir</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">Invoice</th>
                            <th class="px-4 py-2 text-left">Kasir</th>
                            <th class="px-4 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentTransactions as $t)
                        <tr>
                            <td class="px-4 py-2 font-mono text-indigo-600 text-xs">{{ $t->invoice_number }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $t->user->name ?? 'Online' }}</td>
                            <td class="px-4 py-2 text-right font-bold">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- SCRIPT CHART.JS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Line Chart (Tren Penjualan)
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Omset (Rp)',
                    data: @json($chartData),
                    borderColor: '#4F46E5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // 2. Pie Chart (Online vs Offline)
        const ctxPie = document.getElementById('pieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['POS (Toko)', 'Online'],
                datasets: [{
                    data: [{{ $countPos }}, {{ $countOnline }}],
                    backgroundColor: ['#4F46E5', '#A855F7'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: { legend: { display: false } }
            }
        });
    });
</script>
@endsection