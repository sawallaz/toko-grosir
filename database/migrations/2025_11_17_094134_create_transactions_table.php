<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Header Transaksi (Nota)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Cth: INV-20231101-001
            
            // Kasir yang melayani (Bisa NULL jika pesanan online baru masuk dan belum diproses)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('restrict');

            // [BARU] Pembeli Online (User Login) - Terpisah dari Kasir
            $table->foreignId('buyer_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            
            // Pelanggan Member (Data Offline/Manual)
            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained('customers')
                  ->onDelete('set null');
            
            // Data Keuangan
            $table->decimal('total_amount', 15, 2);           // Total Tagihan
            $table->decimal('pay_amount', 15, 2)->default(0); // Uang Dibayar
            $table->decimal('change_amount', 15, 2)->default(0); // Kembalian
            $table->integer('total_items')->default(0);       // Total Qty Barang
            
            // Metode & Status
            $table->string('payment_method')->default('cash'); // cash, transfer, qris
            $table->string('snap_token')->nullable();          // Token Midtrans (Khusus Online)
            
            $table->enum('type', ['pos', 'online'])->default('pos'); 
            
            // PERBAIKAN: Tambahkan status 'ready' ke dalam enum
            $table->enum('status', ['pending', 'process', 'ready', 'completed', 'cancelled'])
                  ->default('pending'); // Default pending untuk pesanan online
            
            // OPSIONAL: Timestamps untuk tracking status (jika ingin ditambahkan)
            $table->timestamp('process_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps(); // Created_at (Waktu Transaksi), updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};