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
        // Tabel detail untuk item-item yang masuk
        Schema::create('stock_entry_details', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke header
            $table->foreignId('stock_entry_id')
                  ->constrained('stock_entries')
                  ->onDelete('cascade');

            // Relasi ke produk+satuan (cth: Indomie Dos)
            $table->foreignId('product_unit_id')
                  ->constrained('product_units')
                  ->onDelete('restrict');

            $table->integer('quantity'); // Jumlah (cth: 10 Dos)
            
            // Harga beli modal pada saat itu (untuk arsip)
            $table->decimal('price_at_entry', 15, 2); 
            $table->decimal('subtotal', 15, 2);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_entry_details');
    }
};