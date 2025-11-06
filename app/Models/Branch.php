<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'phone',
        'email',
        'manager_name',
        'is_active'
    ];

    protected $attributes = [
        'is_active' => true
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function fromMovements()
    {
        return $this->hasMany(ProductMovement::class, 'from_branch_id');
    }

    public function toMovements()
    {
        return $this->hasMany(ProductMovement::class, 'to_branch_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTotalProductsAttribute()
    {
        return $this->products()->count();
    }

    public function getLowStockProductsAttribute()
    {
        return $this->products()->where('quantity', '<=', \DB::raw('reorder_level'))->count();
    }

    public function getInventoryValueAttribute()
    {
        return $this->products()->sum(\DB::raw('quantity * price'));
    }
}