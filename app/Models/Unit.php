<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;
    
    /**
     * [PERBAIKAN]
     * Kita ganti dari $guarded ke $fillable agar lebih jelas
     * dan memaksa Laravel mengenali kolom-kolom ini.
     * Ini akan memperbaiki error "kesalahan jaringan" Anda.
     */
    protected $fillable = [
        'name',
        'short_name',
    ];

    /**
     * Relasi: Satu Satuan bisa dimiliki oleh banyak ProductUnit
     */
    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }
}