<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_wholesale_prices', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Satuan Produk (Bukan Produknya langsung, tapi satuannya)
            // Karena harga grosir Dos beda dengan harga grosir Pcs
            $table->foreignId('product_unit_id')
                  ->constrained('product_units')
                  ->onDelete('cascade');

            $table->integer('min_qty'); // Minimal beli berapa (cth: 10)
            $table->decimal('price', 15, 2); // Harga per unit jika beli segitu (cth: 95000)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_wholesale_prices');
    }
};