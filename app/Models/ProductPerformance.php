<?php
// app/Models/ProductPerformance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPerformance extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'sale_date', 'quantity_sold', 'revenue', 'profit'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'revenue' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}