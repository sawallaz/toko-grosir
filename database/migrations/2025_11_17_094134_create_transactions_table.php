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
            $table->string('invoice_number')->unique();
            
            // Kasir yang memproses (Bisa NULL jika pesanan online baru masuk)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('restrict');
            
            // [BARU] Pembeli Online (User yang login di halaman customer)
            $table->foreignId('buyer_id')->nullable()->constrained('users')->onDelete('set null');

            // Data Member (Opsional, terhubung ke tabel customers)
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            
            $table->decimal('total_amount', 15, 2);
            $table->decimal('pay_amount', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->integer('total_items')->default(0);
            
            $table->string('payment_method')->default('cash'); 
            $table->enum('type', ['pos', 'online'])->default('pos'); 
            
            // [UPDATE] Tambah status 'ready'
            $table->enum('status', ['pending', 'process', 'ready', 'completed', 'cancelled'])->default('completed');
            
            // [BARU] Kolom untuk pengiriman
            $table->enum('delivery_type', ['pickup', 'delivery'])->default('pickup');
            $table->text('delivery_address')->nullable();
            $table->text('delivery_note')->nullable();
            
            // [BARU] Timestamp untuk tracking
            $table->timestamp('ready_at')->nullable();
            
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