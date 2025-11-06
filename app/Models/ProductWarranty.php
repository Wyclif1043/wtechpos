<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductWarranty extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'warranty_name', 'type', 'duration_months', 
        'terms', 'coverage_details', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warrantyClaims()
    {
        return $this->hasMany(WarrantyClaim::class, 'product_warranty_id');
    }

    public function saleWarranties()
    {
        return $this->hasMany(Warranty::class, 'product_warranty_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Filter Scope - ADD THIS METHOD
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['product'] ?? null, function ($query, $productId) {
            $query->where('product_id', $productId);
        })
        ->when($filters['type'] ?? null, function ($query, $type) {
            $query->where('type', $type);
        })
        ->when($filters['status'] ?? null, function ($query, $status) {
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        })
        ->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function($q) use ($search) {
                $q->where('warranty_name', 'like', '%'.$search.'%')
                  ->orWhereHas('product', function($q) use ($search) {
                      $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%');
                  });
            });
        });
    }

    // Methods
    public function getDurationYearsAttribute()
    {
        return $this->duration_months / 12;
    }

    public function getFormattedDurationAttribute()
    {
        if ($this->duration_months >= 12) {
            $years = floor($this->duration_months / 12);
            $months = $this->duration_months % 12;
            
            if ($months > 0) {
                return "{$years} year" . ($years > 1 ? 's' : '') . " {$months} month" . ($months > 1 ? 's' : '');
            }
            return "{$years} year" . ($years > 1 ? 's' : '');
        }
        
        return "{$this->duration_months} month" . ($this->duration_months > 1 ? 's' : '');
    }
}