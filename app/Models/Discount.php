<?php
// app/Models/Discount.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'value', 'scope', 'scope_ids', 'min_purchase_amount',
        'min_quantity', 'max_uses', 'used_count', 'start_date', 'end_date',
        'description', 'apply_automatically', 'is_active'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'scope_ids' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'apply_automatically' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function saleDiscounts()
    {
        return $this->hasMany(SaleDiscount::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('start_date', '<=', Carbon::now())
                    ->where('end_date', '>=', Carbon::now());
    }

    public function scopeAutomatic($query)
    {
        return $query->where('apply_automatically', true);
    }

    public function scopeManual($query)
    {
        return $query->where('apply_automatically', false);
    }

    public function scopeValid($query)
    {
        return $query->active()
                    ->where(function($query) {
                        $query->whereNull('max_uses')
                            ->orWhereRaw('used_count < max_uses');
                    });
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', Carbon::now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', Carbon::now());
    }

    // Accessors & Mutators
    public function getIsValidAttribute()
    {
        return $this->is_active && 
               Carbon::now()->between($this->start_date, $this->end_date) &&
               (!$this->max_uses || $this->used_count < $this->max_uses);
    }

    public function getIsExpiredAttribute()
    {
        return Carbon::now()->gt($this->end_date);
    }

    public function getRemainingUsesAttribute()
    {
        return $this->max_uses ? $this->max_uses - $this->used_count : null;
    }

    public function getDaysRemainingAttribute()
    {
        return Carbon::now()->diffInDays($this->end_date, false);
    }

    public function getFormattedValueAttribute()
    {
        return $this->type === 'percentage' ? 
               $this->value . '%' : 
               'KSh ' . number_format($this->value, 2);
    }

    public function getScopeDescriptionAttribute()
    {
        $descriptions = [
            'sale' => 'Entire Sale',
            'product' => 'Specific Products',
            'category' => 'Product Categories',
            'customer' => 'Specific Customers'
        ];

        return $descriptions[$this->scope] ?? $this->scope;
    }

    // Methods
    public function incrementUses()
    {
        $this->increment('used_count');
    }

    public function canApplyToSale($saleData)
    {
        if (!$this->is_valid) {
            return false;
        }

        // Check minimum purchase amount
        if ($this->min_purchase_amount && $saleData['subtotal'] < $this->min_purchase_amount) {
            return false;
        }

        // Scope-specific checks
        switch ($this->scope) {
            case 'product':
                return $this->checkProductScope($saleData['items']);
            case 'category':
                return $this->checkCategoryScope($saleData['items']);
            case 'customer':
                return $this->checkCustomerScope($saleData['customer_id']);
            case 'sale':
            default:
                return true;
        }
    }

    protected function checkProductScope($items)
    {
        $applicableProducts = $this->scope_ids ?: [];
        
        foreach ($items as $item) {
            if (in_array($item['product_id'], $applicableProducts)) {
                if (!$this->min_quantity || $item['quantity'] >= $this->min_quantity) {
                    return true;
                }
            }
        }
        
        return false;
    }

    protected function checkCategoryScope($items)
    {
        // This would need product category data
        // For now, return true (simplified)
        return true;
    }

    protected function checkCustomerScope($customerId)
    {
        if (!$customerId) return false;
        
        $applicableCustomers = $this->scope_ids ?: [];
        return in_array($customerId, $applicableCustomers);
    }

    public function calculateDiscountAmount($baseAmount, $quantity = 1)
    {
        switch ($this->type) {
            case 'percentage':
                return ($baseAmount * $this->value) / 100;
                
            case 'fixed_amount':
                return min($this->value, $baseAmount);
                
            case 'buy_x_get_y':
                // For buy X get Y free, we need to calculate based on quantity
                $freeItems = floor($quantity / ($this->value + 1)); // value represents X
                return $freeItems * $baseAmount;
                
            default:
                return 0;
        }
    }
}