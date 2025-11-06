<?php
// app/Models/CashDrop.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashDrop extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id', 'amount', 'reason', 'user_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}