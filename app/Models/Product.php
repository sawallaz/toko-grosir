<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'kode_produk', 
        'foto_produk', 
        'category_id',
        'description',
        'stock_in_base_unit',
        'status',
    ];

    /**
     * Relasi: Satu Produk milik satu Kategori
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi: Satu Produk punya banyak Satuan (via ProductUnit)
     */
    public function units()
    {
        return $this->hasMany(ProductUnit::class);
    }

    /**
     * Relasi: Ambil satuan dasar (untuk harga jual dasar & stok)
     */
    public function baseUnit()
    {
        return $this->hasOne(ProductUnit::class)->where('is_base_unit', true);
    }

   
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'active');
    }
}