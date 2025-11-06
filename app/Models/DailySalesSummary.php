<?php
// app/Models/DailySalesSummary.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySalesSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_date', 'total_sales', 'total_amount', 'total_tax', 'total_discount',
        'total_items_sold', 'cash_sales', 'card_sales', 'mobile_money_sales', 'credit_sales'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'mobile_money_sales' => 'decimal:2',
        'credit_sales' => 'decimal:2',
    ];
}