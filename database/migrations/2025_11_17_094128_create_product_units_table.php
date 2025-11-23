<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');

            $table->foreignId('unit_id')
                  ->constrained('units')
                  ->onDelete('cascade');

            // [WAJIB ADA] Ini kolom yang bikin error kemarin!
            // Kita butuh ini untuk menyimpan "Harga Modal Terakhir"
            $table->decimal('harga_beli_modal', 15, 2)->default(0);

            // Harga Jual
            $table->decimal('price', 15, 2)->default(0);

            $table->integer('conversion_to_base');
            $table->boolean('is_base_unit')->default(false);

            $table->unique(['product_id', 'unit_id']);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};