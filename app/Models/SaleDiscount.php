<?php
// app/Models/SaleDiscount.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id', 'discount_id', 'discount_name', 'discount_type',
        'discount_value', 'discount_amount', 'applied_to', 'applied_to_id'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class, 'applied_to_id');
    }
}