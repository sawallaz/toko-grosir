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
            
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            
            // Produk & Satuan apa yang dibeli
            $table->foreignId('product_unit_id')->constrained('product_units')->onDelete('restrict');
            
            $table->integer('quantity');
            $table->decimal('price_at_purchase', 15, 2); // Harga saat dibeli
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