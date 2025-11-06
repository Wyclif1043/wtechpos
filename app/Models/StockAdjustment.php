<?php
// app/Models/StockAdjustment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_number', 'product_id', 'quantity', 'type', 'reason',
        'description', 'previous_stock', 'new_stock', 'cost_value', 'user_id'
    ];

    protected $casts = [
        'cost_value' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($adjustment) {
            $adjustment->adjustment_number = 'ADJ-' . date('Ymd') . '-' . str_pad(StockAdjustment::count() + 1, 4, '0', STR_PAD_LEFT);
        });

        static::created(function ($adjustment) {
            // Update product stock
            $product = $adjustment->product;
            if ($adjustment->type === 'add') {
                $product->increment('stock_quantity', $adjustment->quantity);
            } else {
                $product->decrement('stock_quantity', $adjustment->quantity);
            }

            // Record stock movement
            StockMovement::create([
                'product_id' => $adjustment->product_id,
                'type' => 'adjustment',
                'quantity' => $adjustment->type === 'add' ? $adjustment->quantity : -$adjustment->quantity,
                'previous_stock' => $adjustment->previous_stock,
                'new_stock' => $adjustment->new_stock,
                'unit_cost' => $adjustment->cost_value,
                'reference_type' => StockAdjustment::class,
                'reference_id' => $adjustment->id,
                'reason' => $adjustment->reason . ': ' . $adjustment->description,
                'user_id' => $adjustment->user_id
            ]);
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}