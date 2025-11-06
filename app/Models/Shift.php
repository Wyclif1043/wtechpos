<?php
// app/Models/Shift.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_number', 'user_id', 'starting_cash', 'ending_cash', 'expected_cash',
        'cash_sales', 'card_sales', 'mobile_sales', 'credit_sales', 'total_sales',
        'transaction_count', 'refunds_amount', 'refunds_count', 'started_at',
        'ended_at', 'status', 'notes'
    ];

    protected $casts = [
        'starting_cash' => 'decimal:2',
        'ending_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'mobile_sales' => 'decimal:2',
        'credit_sales' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'refunds_amount' => 'decimal:2',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shift) {
            $shift->shift_number = 'SHIFT-' . date('Ymd') . '-' . str_pad(Shift::count() + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashDrops()
    {
        return $this->hasMany(CashDrop::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'user_id', 'user_id')
                    ->whereBetween('created_at', [$this->started_at, $this->ended_at ?? now()]);
    }

    // Methods
    public function getDurationAttribute()
    {
        if (!$this->ended_at) {
            return $this->started_at->diffForHumans(now(), true);
        }
        
        return $this->started_at->diffForHumans($this->ended_at, true);
    }

    public function getCurrentCashAttribute()
    {
        $totalCashDrops = $this->cashDrops()->sum('amount');
        return $this->starting_cash + $this->cash_sales - $totalCashDrops;
    }

    public function getCashDifferenceAttribute()
    {
        if (!$this->ending_cash) {
            return null;
        }
        
        return $this->ending_cash - $this->expected_cash;
    }

    public function getIsBalancedAttribute()
    {
        if (!$this->ending_cash || !$this->expected_cash) {
            return null;
        }
        
        return abs($this->cash_difference) <= 0.01; // Allow 1 cent difference
    }

    public function updateSalesData()
    {
        $sales = $this->sales()->get();
        
        $this->update([
            'cash_sales' => $sales->where('payment_status', 'paid')->sum(function($sale) {
                return $sale->payments()->where('payment_method', 'cash')->sum('amount');
            }),
            'card_sales' => $sales->where('payment_status', 'paid')->sum(function($sale) {
                return $sale->payments()->where('payment_method', 'card')->sum('amount');
            }),
            'mobile_sales' => $sales->where('payment_status', 'paid')->sum(function($sale) {
                return $sale->payments()->where('payment_method', 'mobile_money')->sum('amount');
            }),
            'credit_sales' => $sales->where('payment_status', 'paid')->sum(function($sale) {
                return $sale->payments()->where('payment_method', 'credit')->sum('amount');
            }),
            'total_sales' => $sales->where('payment_status', 'paid')->sum('total_amount'),
            'transaction_count' => $sales->count(),
            'refunds_amount' => $sales->sum('total_returned'),
            'refunds_count' => $sales->where('total_returned', '>', 0)->count(),
        ]);

        // Calculate expected cash
        $totalCashDrops = $this->cashDrops()->sum('amount');
        $this->expected_cash = $this->starting_cash + $this->cash_sales - $totalCashDrops;
        $this->save();
    }

    public function endShift($endingCash, $notes = null)
    {
        $this->update([
            'ending_cash' => $endingCash,
            'ended_at' => now(),
            'status' => 'ended',
            'notes' => $notes,
        ]);

        return $this;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('started_at', today());
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }
}