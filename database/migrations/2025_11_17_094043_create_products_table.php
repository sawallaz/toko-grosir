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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            $table->string('kode_produk')->unique(); // SKU (Stock Keeping Unit)

            // [BARU] Kolom untuk foto produk
            $table->string('foto_produk')->nullable();
            
            // Relasi ke tabel categories
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('categories')
                  ->onDelete('set null'); // Jika kategori dihapus, set null

            $table->text('description')->nullable();
            
            // Stok dalam satuan terkecil (base unit), cth: Pcs
            $table->integer('stock_in_base_unit')->default(0);

            // Status produk (aktif / nonaktif)
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};