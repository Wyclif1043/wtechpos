<?php
// app/Models/SaleReturn.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    use HasFactory;

    protected $table = 'returns'; // keep the same table name if needed

    protected $fillable = [
        'return_number', 'sale_id', 'customer_id', 'user_id', 'total_amount',
        'refund_amount', 'reason', 'notes', 'status', 'refund_method',
        'refund_reference', 'processed_at', 'refunded_at'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($return) {
            $return->return_number = 'RET-' . date('Ymd') . '-' . str_pad(SaleReturn::count() + 1, 4, '0', STR_PAD_LEFT);
        });

        static::updated(function ($return) {
            if ($return->isDirty('status') && $return->status === 'completed') {
                foreach ($return->items as $item) {
                    $product = $item->product;
                    if ($product->track_stock && in_array($item->condition, ['new', 'opened'])) {
                        $product->increment('stock_quantity', $item->quantity_returned);
                        
                        StockMovement::create([
                            'product_id' => $product->id,
                            'type' => 'return',
                            'quantity' => $item->quantity_returned,
                            'previous_stock' => $product->stock_quantity - $item->quantity_returned,
                            'new_stock' => $product->stock_quantity,
                            'unit_cost' => $item->unit_price,
                            'reference_type' => SaleReturn::class,
                            'reference_id' => $return->id,
                            'reason' => 'Product return: ' . $return->reason,
                            'user_id' => $return->user_id,
                        ]);
                    }
                }
            }
        });
    }

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(ReturnItem::class);
    }

    // Accessors
    public function getIsProcessedAttribute()
    {
        return !is_null($this->processed_at);
    }

    public function getIsRefundedAttribute()
    {
        return !is_null($this->refunded_at);
    }

    public function getCanRefundAttribute()
    {
        return $this->status === 'approved' && !$this->is_refunded;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRefunded($query)
    {
        return $query->whereNotNull('refunded_at');
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
