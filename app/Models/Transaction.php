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
        'buyer_id', 
        'customer_id',
        'total_amount',
        'pay_amount',       // [BARU]
        'change_amount',    // [BARU]
        'total_items',      // [BARU]
        'payment_method',
        'type',
        'status',
        'delivery_type',
        'delivery_address',
        'delivery_note',
        'ready_at',         // [BARU]
    ];

    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    
    // [BARU] Relasi ke Pembeli Online
    public function buyer() { return $this->belongsTo(User::class, 'buyer_id'); }
    
    public function customer() { return $this->belongsTo(Customer::class); }
    
    public function details() { return $this->hasMany(TransactionDetail::class); }
}