<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'from_branch_id',
        'to_branch_id',
        'quantity',
        'status',
        'reason',
        'requested_by',
        'approved_by',
        'processed_by',
        'reference_number',
        'notes',
        'approved_at',
        'shipped_at',
        'delivered_at',
        'tracking_number',
        'document_path'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
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

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('from_branch_id', $branchId)
                    ->orWhere('to_branch_id', $branchId);
    }

    // Methods
    public static function generateReferenceNumber()
    {
        do {
            $reference = 'TRF' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('reference_number', $reference)->exists());

        return $reference;
    }

    public function canBeApproved()
    {
        return $this->status === 'pending' && $this->hasSufficientStock();
    }

    public function canBeShipped()
    {
        return $this->status === 'approved';
    }

    public function canBeDelivered()
    {
        return $this->status === 'shipped';
    }

    public function hasSufficientStock()
    {
        $sourceProduct = Product::where('id', $this->product_id)
            ->where('branch_id', $this->from_branch_id)
            ->first();

        return $sourceProduct && $sourceProduct->hasSufficientStock($this->quantity);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'shipped' => 'bg-purple-100 text-purple-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    // Add these methods to your ProductMovement model
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function canBeReceived()
    {
        return $this->status === 'shipped';
    }

    public function getReceiptUrlAttribute()
    {
        return $this->receipt_path ? Storage::disk('public')->url($this->receipt_path) : null;
    }

    public function getDocumentUrlAttribute()
    {
        return $this->document_path ? asset('storage/' . $this->document_path) : null;
    }
}