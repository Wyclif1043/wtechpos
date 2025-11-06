<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Warranty extends Model
{
    use HasFactory;

    protected $fillable = [
        'warranty_number', 'sale_id', 'sale_item_id', 'customer_id', 'product_id',
        'type', 'duration_months', 'start_date', 'end_date', 'terms', 'status', 'notes',
        'serial_number', 'batch_number' // Add these fields
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($warranty) {
            $warranty->warranty_number = 'WRN-' . date('Ymd') . '-' . str_pad(Warranty::count() + 1, 4, '0', STR_PAD_LEFT);
        });

        // Auto-update status based on dates
        static::saving(function ($warranty) {
            if ($warranty->end_date && $warranty->end_date->isPast()) {
                $warranty->status = 'expired';
            }
        });
    }

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function claims()
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    // Methods
    public function getRemainingDaysAttribute()
    {
        if ($this->status !== 'active') {
            return 0;
        }

        return Carbon::now()->diffInDays($this->end_date, false);
    }

    public function getIsExpiredAttribute()
    {
        return $this->end_date->isPast();
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active' && !$this->is_expired;
    }

    public function getCoverageProgressAttribute()
    {
        $totalDays = $this->start_date->diffInDays($this->end_date);
        $daysPassed = $this->start_date->diffInDays(Carbon::now());
        
        return min(100, max(0, ($daysPassed / $totalDays) * 100));
    }

    // Check if warranty can have claims
    public function getCanFileClaimAttribute()
    {
        return $this->is_active && 
               $this->saleItem->quantity_remaining > 0 && // Item hasn't been fully returned
               !$this->sale->is_fully_returned; // Sale hasn't been fully returned
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('end_date', '>=', Carbon::now());
    }

    public function scopeExpired($query)
    {
        return $query->where(function($q) {
            $q->where('status', 'expired')
              ->orWhere('end_date', '<', Carbon::now());
        });
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'active')
                    ->whereBetween('end_date', [Carbon::now(), Carbon::now()->addDays($days)]);
    }

    // Filter Scope
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['status'] ?? null, function ($query, $status) {
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'expired') {
                $query->expired();
            } else {
                $query->where('status', $status);
            }
        })
        ->when($filters['type'] ?? null, function ($query, $type) {
            $query->where('type', $type);
        })
        ->when($filters['customer'] ?? null, function ($query, $customerId) {
            $query->where('customer_id', $customerId);
        })
        ->when($filters['product'] ?? null, function ($query, $productId) {
            $query->where('product_id', $productId);
        });
    }
}