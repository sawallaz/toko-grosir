<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockEntryDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_entry_id',
        'product_unit_id',
        'quantity',
        'price_at_entry', // Harga beli saat transaksi
        'subtotal'
    ];

    public function stockEntry()
    {
        return $this->belongsTo(StockEntry::class);
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class);
    }
}