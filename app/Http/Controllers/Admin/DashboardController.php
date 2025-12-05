<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 1. KARTU STATISTIK
        $omsetHariIni = Transaction::whereDate('created_at', $today)->where('status', 'completed')->sum('total_amount');
        $transaksiHariIni = Transaction::whereDate('created_at', $today)->where('status', 'completed')->count();
        $totalPelanggan = Customer::count();
        
        // Hitung Estimasi Profit (Sederhana: Total Jual - Total Modal)
        // Catatan: Ini asumsi kasar berdasarkan selisih harga di tabel detail
        // Di real world, perlu history harga beli yang presisi.
        $profitHariIni = 0;
        $trxHariIni = Transaction::with('details.productUnit')->whereDate('created_at', $today)->where('status', 'completed')->get();
        foreach($trxHariIni as $trx) {
            foreach($trx->details as $d) {
                $modal = $d->productUnit->harga_beli_modal ?? 0;
                $jual = $d->price_at_purchase;
                $profitHariIni += ($jual - $modal) * $d->quantity;
            }
        }

        // 2. GRAFIK TREN PENJUALAN (7 HARI TERAKHIR)
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartLabels[] = $date->format('d M');
            $chartData[] = Transaction::whereDate('created_at', $date->format('Y-m-d'))
                ->where('status', 'completed')
                ->sum('total_amount');
        }

        // 3. KOMPOSISI ONLINE VS POS
        $countPos = Transaction::where('type', 'pos')->where('status', 'completed')->count();
        $countOnline = Transaction::where('type', 'online')->where('status', 'completed')->count();

        // 4. STOK MENIPIS (Alert)
        // Ambil produk yang stok base unit-nya kurang dari 10
        $lowStockProducts = Product::with(['baseUnit.unit', 'category'])
            ->where('stock_in_base_unit', '<=', 10)
            ->orderBy('stock_in_base_unit', 'asc')
            ->limit(5)
            ->get();

        // 5. TRANSAKSI TERBARU
        $recentTransactions = Transaction::with('user')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'omsetHariIni', 'transaksiHariIni', 'totalPelanggan', 'profitHariIni',
            'chartLabels', 'chartData', 'countPos', 'countOnline',
            'lowStockProducts', 'recentTransactions'
        ));
    }
}