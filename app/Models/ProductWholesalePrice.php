<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductWholesalePrice extends Model
{
    use HasFactory;
    protected $fillable = ['product_unit_id', 'min_qty', 'price'];
}