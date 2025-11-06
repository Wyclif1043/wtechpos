<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WarrantyClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_number', 'sale_id', 'sale_item_id', 'customer_id', 'product_id', 
        'product_warranty_id', 'claim_date', 'issue_type', 'problem_description', 
        'resolution_notes', 'repair_cost', 'status', 'resolution_date', 
        'resolved_by', 'customer_feedback'
    ];

    protected $casts = [
        'claim_date' => 'date',
        'resolution_date' => 'date',
        'repair_cost' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($claim) {
            $claim->claim_number = 'WCL-' . date('Ymd') . '-' . str_pad(WarrantyClaim::count() + 1, 4, '0', STR_PAD_LEFT);
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

    public function productWarranty()
    {
        return $this->belongsTo(ProductWarranty::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Methods
    public function getDaysToResolveAttribute()
    {
        if (!$this->resolution_date) {
            return null;
        }

        return $this->claim_date->diffInDays($this->resolution_date);
    }

    public function getIsResolvedAttribute()
    {
        return in_array($this->status, ['approved', 'rejected', 'completed']);
    }

    public function getIsUnderWarrantyAttribute()
    {
        if (!$this->sale || !$this->productWarranty) {
            return false;
        }

        $warrantyEndDate = $this->sale->created_at->copy()->addMonths($this->productWarranty->duration_months);
        return now()->lte($warrantyEndDate);
    }

    public function getWarrantyEndDateAttribute()
    {
        if (!$this->sale || !$this->productWarranty) {
            return null;
        }

        return $this->sale->created_at->copy()->addMonths($this->productWarranty->duration_months);
    }

    public function getWarrantyRemainingDaysAttribute()
    {
        if (!$this->is_under_warranty) {
            return 0;
        }

        return now()->diffInDays($this->warranty_end_date, false);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('status', ['submitted', 'in_progress']);
    }

    public function scopeResolved($query)
    {
        return $query->whereIn('status', ['approved', 'rejected', 'completed']);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeUnderWarranty($query)
    {
        // Get the maximum warranty duration from product warranties
        $maxWarrantyMonths = ProductWarranty::max('duration_months') ?? 24;
        $cutoffDate = now()->subMonths($maxWarrantyMonths);
        
        return $query->whereHas('sale', function($q) use ($cutoffDate) {
            $q->where('created_at', '>=', $cutoffDate);
        })->whereHas('productWarranty');
    }

    // Filter Scope - FIXED VERSION
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })
        ->when($filters['customer'] ?? null, function ($query, $customerId) {
            $query->where('customer_id', $customerId);
        })
        ->when($filters['product'] ?? null, function ($query, $productId) {
            $query->where('product_id', $productId);
        })
        ->when($filters['issue_type'] ?? null, function ($query, $issueType) {
            $query->where('issue_type', $issueType);
        })
        ->when($filters['date_from'] ?? null, function ($query, $dateFrom) {
            $query->where('claim_date', '>=', $dateFrom);
        })
        ->when($filters['date_to'] ?? null, function ($query, $dateTo) {
            $query->where('claim_date', '<=', $dateTo);
        });
    }
}