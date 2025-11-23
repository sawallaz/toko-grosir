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
        // Tabel header untuk mencatat "Input Stok Baru"
        Schema::create('stock_entries', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique(); // No. Transaksi (cth: TRX-STK-20231115-001)
            
            // Siapa yang input (Dibuat Oleh)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict');

            // [SESUAI PERMINTAAN] Supplier (bisa kosong)
            $table->foreignId('supplier_id')
                  ->nullable()
                  ->constrained('suppliers')
                  ->onDelete('set null');

            $table->date('entry_date'); // Tanggal
            $table->text('notes')->nullable(); // Catatan tambahan (jika perlu)
            $table->decimal('total_value', 15, 2)->default(0); // Total nilai pembelian
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_entries');
    }
};