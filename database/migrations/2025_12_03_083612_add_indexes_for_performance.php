<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Percepat Pencarian Produk (Scan Barcode & Ketik Nama)
        Schema::table('products', function (Blueprint $table) {
            $table->index('name');        // Biar cari nama ngebut
            $table->index('kode_produk'); // Biar scan barcode instan
            $table->index('status');      // Biar filter aktif/nonaktif cepat
        });

        // 2. Percepat Pencarian Transaksi & Laporan
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('invoice_number'); // Cari nota
            $table->index('created_at');     // Filter tanggal laporan
            $table->index('type');           // Filter Online/POS
            $table->index('status');         // Filter Pending/Completed
        });

        // 3. Percepat Pencarian Member/Customer
        Schema::table('customers', function (Blueprint $table) {
            $table->index('phone'); // Cari member by HP
            $table->index('name');  // Cari member by Nama
        });
        
        // 4. Percepat Riwayat Stok
        Schema::table('stock_entries', function (Blueprint $table) {
            $table->index('transaction_number');
            $table->index('entry_date');
        });
    }

    public function down(): void
    {
        // Hapus index jika rollback
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name', 'kode_produk', 'status']);
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['invoice_number', 'created_at', 'type', 'status']);
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['phone', 'name']);
        });
        Schema::table('stock_entries', function (Blueprint $table) {
            $table->dropIndex(['transaction_number', 'entry_date']);
        });
    }
};