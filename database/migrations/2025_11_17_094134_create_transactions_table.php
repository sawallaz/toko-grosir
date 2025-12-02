<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();

            // Kasir
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('restrict');

            // Pembeli Online
            $table->foreignId('buyer_id')->nullable()->constrained('users')->onDelete('set null');

            // Member / Customer
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');

            // Nominal
            $table->decimal('total_amount', 15, 2);
            $table->decimal('pay_amount', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->integer('total_items')->default(0);

            // Payment
            $table->string('payment_method')->nullable(); // dari add-col migration
            $table->string('payment_status')->default('pending'); 
            $table->string('payment_channel')->nullable();

            // Midtrans
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->string('midtrans_snap_token')->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->text('midtrans_response')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            // Type transaksi
            $table->enum('type', ['pos', 'online'])->default('pos');

            // Status transaksi
            $table->enum('status', ['pending', 'process', 'ready', 'completed', 'cancelled'])
                  ->default('completed');

            // Pengiriman
            $table->enum('delivery_type', ['pickup', 'delivery'])->default('pickup');
            $table->text('delivery_address')->nullable();
            $table->text('delivery_note')->nullable();

            // Tracking
            $table->timestamp('ready_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
