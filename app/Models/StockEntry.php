<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockEntry extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'transaction_number',
        'user_id',       // Wajib ada
        'supplier_id',
        'entry_date',
        'notes',
        'total_value'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details()
    {
        return $this->hasMany(StockEntryDetail::class);
    }
}