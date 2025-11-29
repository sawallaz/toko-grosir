<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_unit_id',
        'quantity',
        'price_at_purchase',
        'subtotal',
    ];

    // Relasi balik ke Header Transaksi
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Relasi ke Produk Unit (Untuk tahu barang apa & satuan apa)
    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }
}