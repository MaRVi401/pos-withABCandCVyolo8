<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    protected $primaryKey = 'transaction_id';
    public $timestamps = false;

    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    protected $fillable = [
        'invoice_number',
        'admin_id',
        'customer_id',
        'transaction_date',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'final_amount',
        'status',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'user_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id', 'transaction_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'transaction_id', 'transaction_id');
    }
}