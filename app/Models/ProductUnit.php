<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    use HasFactory;

    // [WAJIB] Pastikan 'harga_beli_modal' ada di sini!
    protected $fillable = [
        'product_id',
        'unit_id',
        'harga_beli_modal', 
        'price',
        'conversion_to_base',
        'is_base_unit',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}