<?php
// app/Models/Customer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'address', 
        'loyalty_points', 'credit_balance', 'total_spent', 'last_purchase'
    ];

    protected $casts = [
        'loyalty_points' => 'decimal:2',
        'credit_balance' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'last_purchase' => 'date',
    ];

    // Relationships
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function creditTransactions()
    {
        return $this->hasMany(CustomerCreditTransaction::class);
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
    }

    public function scopeWithCredit($query)
    {
        return $query->where('credit_balance', '>', 0);
    }

    public function scopeWithLoyaltyPoints($query)
    {
        return $query->where('loyalty_points', '>', 0);
    }

    // Methods
    public function addCreditSale($amount, $sale = null)
    {
        $previousBalance = $this->credit_balance;
        $this->increment('credit_balance', $amount);
        
        CustomerCreditTransaction::create([
            'customer_id' => $this->id,
            'type' => 'credit_sale',
            'amount' => $amount,
            'previous_balance' => $previousBalance,
            'new_balance' => $this->credit_balance,
            'reference_type' => $sale ? Sale::class : null,
            'reference_id' => $sale ? $sale->id : null,
            'notes' => 'Credit sale',
            'user_id' => auth()->id() ?? 1,
        ]);
    }

    public function addPayment($amount, $notes = null)
    {
        $previousBalance = $this->credit_balance;
        $this->decrement('credit_balance', $amount);
        
        CustomerCreditTransaction::create([
            'customer_id' => $this->id,
            'type' => 'payment',
            'amount' => -$amount,
            'previous_balance' => $previousBalance,
            'new_balance' => $this->credit_balance,
            'notes' => $notes,
            'user_id' => auth()->id() ?? 1,
        ]);
    }

    public function addLoyaltyPoints($points, $reference = null, $notes = null)
    {
        $previousPoints = $this->loyalty_points;
        $this->increment('loyalty_points', $points);
        
        LoyaltyTransaction::create([
            'customer_id' => $this->id,
            'type' => 'earn',
            'points' => $points,
            'previous_points' => $previousPoints,
            'new_points' => $this->loyalty_points,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
            'notes' => $notes,
        ]);
    }

    public function redeemLoyaltyPoints($points, $notes = null)
    {
        if ($points > $this->loyalty_points) {
            throw new \Exception('Insufficient loyalty points');
        }

        $previousPoints = $this->loyalty_points;
        $this->decrement('loyalty_points', $points);
        
        LoyaltyTransaction::create([
            'customer_id' => $this->id,
            'type' => 'redeem',
            'points' => -$points,
            'previous_points' => $previousPoints,
            'new_points' => $this->loyalty_points,
            'notes' => $notes,
        ]);
    }

    public function getLoyaltyTierAttribute()
    {
        if ($this->total_spent >= 1000) return 'Gold';
        if ($this->total_spent >= 500) return 'Silver';
        if ($this->total_spent >= 100) return 'Bronze';
        return 'Standard';
    }

    public function getLoyaltyDiscountRateAttribute()
    {
        return match($this->loyalty_tier) {
            'Gold' => 0.10, // 10%
            'Silver' => 0.05, // 5%
            'Bronze' => 0.02, // 2%
            default => 0,
        };
    }

    public function warranties()
    {
        return $this->hasMany(Warranty::class);
    }

    public function warrantyClaims()
    {
        return $this->hasMany(WarrantyClaim::class);
    }
}