<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            
            // Siapa kasir/admin yang proses
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict'); 

            // Siapa customer-nya (jika terdaftar)
            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained('users') // Relasi ke tabel users juga
                  ->onDelete('set null');

            $table->decimal('total_amount', 15, 2);
            $table->string('payment_method')->default('cash');
            
            // 'pos' = Penjualan langsung, 'online' = Pesanan online
            $table->enum('type', ['pos', 'online'])->default('pos');
            
            // 'completed' = Selesai, 'pending' = Menunggu (utk online)
            $table->enum('status', ['completed', 'pending', 'cancelled'])->default('pending');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};