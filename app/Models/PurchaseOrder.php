<?php
// app/Models/PurchaseOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number', 'supplier_id', 'user_id', 'total_amount', 'status',
        'order_date', 'expected_delivery_date', 'received_date', 'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'received_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchaseOrder) {
            if (empty($purchaseOrder->po_number)) {
                $purchaseOrder->po_number = 'PO-' . date('Ymd') . '-' . str_pad(PurchaseOrder::count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    // Helper methods
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity_ordered');
    }

    public function getTotalReceivedAttribute()
    {
        return $this->items->sum('quantity_received');
    }

    public function getRemainingItemsAttribute()
    {
        return $this->total_items - $this->total_received;
    }

    public function canBeEdited()
    {
        return $this->status === 'pending';
    }

    public function canBeReceived()
    {
        return in_array($this->status, ['pending', 'partially_received']);
    }

    public function canBeCancelled()
    {
        return $this->status === 'pending';
    }
}