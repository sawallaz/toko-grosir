<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Lindungi kolom yang tidak boleh diisi massal
    protected $guarded = ['id'];

    /**
     * Relasi: Satu Kategori memiliki banyak Produk
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}