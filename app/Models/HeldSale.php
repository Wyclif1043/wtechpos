<?php
// app/Models/HeldSale.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HeldSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'hold_number', 'user_id', 'customer_id', 'cart_data', 'subtotal',
        'tax_amount', 'discount_amount', 'total_amount', 'notes', 'status'
    ];

    protected $casts = [
        'cart_data' => 'array',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'held_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($heldSale) {
            if (empty($heldSale->hold_number)) {
                $heldSale->hold_number = 'HOLD-' . date('Ymd') . '-' . str_pad(HeldSale::count() + 1, 4, '0', STR_PAD_LEFT);
            }
            
            $heldSale->held_at = $heldSale->held_at ?? now();
            $heldSale->expires_at = $heldSale->expires_at ?? now()->addHours(24); // 24 hour expiry
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Accessors
    public function getIsExpiredAttribute()
    {
        return $this->expires_at && Carbon::now()->gt($this->expires_at);
    }

    public function getTimeRemainingAttribute()
    {
        if (!$this->expires_at) return null;
        
        return Carbon::now()->diffInMinutes($this->expires_at, false);
    }

    public function getFormattedTotalAttribute()
    {
        return 'KSh ' . number_format($this->total_amount, 2);
    }

    public function getItemCountAttribute()
    {
        return count($this->cart_data ?: []);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'held')
                    ->where(function($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', Carbon::now());
                    });
    }

    public function scopeExpired($query)
    {
        return $query->where(function($query) {
            $query->where('status', 'held')
                ->where('expires_at', '<=', Carbon::now());
        });
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Methods
    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsCancelled()
    {
        $this->update(['status' => 'cancelled']);
    }

    public function extendHold($hours = 24)
    {
        $this->update([
            'expires_at' => Carbon::now()->addHours($hours)
        ]);
    }

    public function restoreToSession()
    {
        session()->put('pos.cart', $this->cart_data);
        $this->markAsCompleted();
    }
}