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
        // 1. Tabel Header Transaksi (Nota)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Cth: INV-20231101-001
            
            // Kasir yang melayani
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            
            // Pelanggan (Bisa kosong/Umum)
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            
            // Keuangan
            $table->decimal('total_amount', 15, 2);
            $table->decimal('pay_amount', 15, 2)->default(0);     // [GABUNG SINI]
            $table->decimal('change_amount', 15, 2)->default(0);  // [GABUNG SINI]
            $table->integer('total_items')->default(0);           // [GABUNG SINI]
            
            $table->string('payment_method')->default('cash'); 
            $table->enum('type', ['pos', 'online'])->default('pos'); 
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            
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