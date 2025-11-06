<?php
// app/Models/CustomerCreditTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCreditTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'type', 'amount', 'previous_balance', 'new_balance',
        'reference_type', 'reference_id', 'notes', 'user_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'new_balance' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}