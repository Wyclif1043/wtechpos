<?php
// app/Models/Sale.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number', 'customer_id', 'user_id', 'subtotal', 'tax_amount',
        'discount_amount', 'total_amount', 'total_paid', 'balance_due',
        'payment_status', 'notes'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        // Generate sale number before creating
        static::creating(function ($sale) {
            if (empty($sale->sale_number)) {
                $sale->sale_number = 'SALE-' . date('Ymd') . '-' . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
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
        return $this->hasMany(SaleItem::class);
    }

    public function warranties()
    {
        return $this->hasMany(Warranty::class);
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function getTotalReturnedAttribute()
    {
        return $this->returns()->where('status', 'completed')->sum('refund_amount');
    }

    public function getIsFullyReturnedAttribute()
    {
        $totalSold = $this->items()->sum('total_price');
        $totalReturned = $this->total_returned;
        
        return $totalReturned >= $totalSold;
    }

    public function saleDiscounts()
    {
        return $this->hasMany(SaleDiscount::class);
    }

    public function appliedDiscounts()
    {
        return $this->hasMany(SaleDiscount::class);
    }

    public function getTotalDiscountAttribute()
    {
        return $this->saleDiscounts()->sum('discount_amount');
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function updatePaymentStatus()
    {
        $totalPaid = $this->payments()->sum('amount');
        $balanceDue = $this->total_amount - $totalPaid;
        
        $paymentStatus = 'pending';
        if ($balanceDue <= 0) {
            $paymentStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partial';
        }
        
        $this->update([
            'total_paid' => $totalPaid,
            'balance_due' => max(0, $balanceDue),
            'payment_status' => $paymentStatus,
        ]);
        
        return $paymentStatus;
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceDueAttribute()
    {
        return max(0, $this->total_amount - $this->total_paid);
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->balance_due <= 0) {
            return 'paid';
        } elseif ($this->total_paid > 0) {
            return 'partial';
        } else {
            return 'pending';
        }
    }

    public function addPayment($paymentMethod, $amount, $reference = null, $notes = null)
    {
        $payment = $this->payments()->create([
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'reference' => $reference,
            'notes' => $notes,
            'user_id' => auth()->id(),
        ]);
        
        $this->updatePaymentStatus();
        
        return $payment;
    }

    public function getPaymentSummaryAttribute()
    {
        $summary = [];
        $payments = $this->payments()->get();
        
        foreach ($payments as $payment) {
            if (!isset($summary[$payment->payment_method])) {
                $summary[$payment->payment_method] = 0;
            }
            $summary[$payment->payment_method] += $payment->amount;
        }
        
        return $summary;
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'user_id', 'user_id')
                    ->where('started_at', '<=', $this->created_at)
                    ->where(function($query) {
                        $query->where('ended_at', '>=', $this->created_at)
                            ->orWhereNull('ended_at');
                    });
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePartial($query)
    {
        return $query->where('payment_status', 'partial');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }
}