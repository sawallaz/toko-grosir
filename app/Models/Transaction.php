<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'customer_id',
        'total_amount',
        'pay_amount',       // [BARU]
        'change_amount',    // [BARU]
        'total_items',      // [BARU]
        'payment_method',
        'type',
        'status',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function details() { return $this->hasMany(TransactionDetail::class); }
}