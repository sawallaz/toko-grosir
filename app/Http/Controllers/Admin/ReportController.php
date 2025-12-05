<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function getFilteredQuery(Request $request)
    {
        // [UPDATE] Eager load detail produk & unit agar bisa ditampilkan di accordion
        $query = Transaction::with([
                'user', 
                'customer',
                'details.productUnit.product', // Load Nama Produk
                'details.productUnit.unit'     // Load Nama Satuan
            ])
            ->where('type', 'pos')
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('customer', function($c) use ($search) {
                      $c->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00', 
                $request->end_date . ' 23:59:59'
            ]);
        }

        return $query;
    }

    public function sales(Request $request)
    {
        $query = $this->getFilteredQuery($request);

        $statsQuery = $query->clone();
        // Optimization: Clear eager loads for count/sum
        $statsQuery->setEagerLoads([]); 
        
        $totalRevenue = $statsQuery->sum('total_amount');
        $totalTransactions = $statsQuery->count();
        $averageTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
        $totalItemsSold = $statsQuery->sum('total_items');

        $cashierStats = $query->clone()
            ->reorder()
            ->select('user_id', DB::raw('sum(total_amount) as revenue'), DB::raw('count(*) as count'))
            ->groupBy('user_id')
            ->with('user')
            ->get();

        $sales = $query->paginate(20)->withQueryString();

        return view('admin.reports.sales.index', compact(
            'sales', 'totalRevenue', 'totalTransactions', 'averageTransaction', 'totalItemsSold', 'cashierStats'
        ));
    }

    // Export ke Excel (CSV)
    public function exportExcel(Request $request)
    {
        $fileName = 'laporan_penjualan_' . date('Y-m-d_H-i') . '.csv';
        $query = $this->getFilteredQuery($request); 
        $transactions = $query->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Header Kolom Excel
            fputcsv($file, ['No Invoice', 'Tanggal', 'Waktu', 'Kasir', 'Pelanggan', 'Total Item', 'Total Bayar', 'Status']);

            foreach ($transactions as $trx) {
                fputcsv($file, [
                    $trx->invoice_number,
                    $trx->created_at->format('Y-m-d'),
                    $trx->created_at->format('H:i:s'),
                    $trx->user->name ?? 'User Hapus',
                    $trx->customer->name ?? 'Umum',
                    $trx->total_items,
                    $trx->total_amount, 
                    $trx->status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        try {
            Transaction::whereIn('id', $request->ids)->delete();
            return back()->with('success', count($request->ids) . ' Data berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal hapus.');
        }
    }
}