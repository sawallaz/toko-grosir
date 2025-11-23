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
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke transaksi
            $table->foreignId('transaction_id')
                  ->constrained('transactions')
                  ->onDelete('cascade');

            // PENTING: Relasi ke product_units
            // Agar kita tahu dia beli produk itu dalam satuan apa (Dos/Pcs)
            $table->foreignId('product_unit_id')
                  ->constrained('product_units')
                  ->onDelete('restrict'); // Jangan hapus data ini jika transaksi ada

            $table->integer('quantity'); // Cth: 2 (Dos)
            
            // Simpan harga saat pembelian (untuk arsip, jika harga produk berubah)
            $table->decimal('price_at_purchase', 15, 2);
            $table->decimal('subtotal', 15, 2);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};