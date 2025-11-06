<?php
// app/Models/SalePayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id', 'payment_method', 'amount', 'reference', 'notes', 'user_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Methods
    public function getPaymentMethodTextAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->payment_method));
    }

    // Scopes
    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeCash($query)
    {
        return $query->where('payment_method', 'cash');
    }

    public function scopeCard($query)
    {
        return $query->where('payment_method', 'card');
    }

    public function scopeMobileMoney($query)
    {
        return $query->where('payment_method', 'mobile_money');
    }

    public function scopeCredit($query)
    {
        return $query->where('payment_method', 'credit');
    }
}