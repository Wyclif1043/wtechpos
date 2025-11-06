<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'barcode', 'sku', 'category_id', 'supplier_id',
        'purchase_price', 'selling_price', 'stock_quantity',
        'min_stock', 'max_stock', 'unit', 'description',
        'image', 'is_active', 'track_stock', 'branch_id'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
        'track_stock' => 'boolean',
    ];

    // Add branch relationship
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Add product movements relationship
    public function movements()
    {
        return $this->hasMany(ProductMovement::class);
    }

    // Add services relationship (for beauty shop services)
    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_product')
                    ->withPivot('quantity_used')
                    ->withTimestamps();
    }

    // Update existing relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    // Update stock status to work with branch system
    public function getStockStatusAttribute()
    {
        if (!$this->track_stock) {
            return 'not_tracked';
        }

        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->stock_quantity <= $this->min_stock) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    public function getStockValueAttribute()
    {
        return $this->stock_quantity * $this->purchase_price;
    }

    public function getMonthlySalesAttribute()
    {
        return $this->saleItems()
            ->whereHas('sale', function ($query) {
                $query->where('created_at', '>=', now()->subMonth());
            })
            ->sum('quantity');
    }

    // Add the missing filter scope - updated for branch system
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%')
                    ->orWhere('barcode', 'like', '%'.$search.'%');
            });
        })->when($filters['category'] ?? null, function ($query, $category) {
            $query->where('category_id', $category);
        })->when($filters['branch'] ?? null, function ($query, $branch) {
            $query->where('branch_id', $branch);
        })->when($filters['stock_status'] ?? null, function ($query, $status) {
            if ($status === 'low') {
                $query->where('track_stock', true)
                    ->whereRaw('stock_quantity <= min_stock AND stock_quantity > 0');
            } elseif ($status === 'out') {
                $query->where('track_stock', true)
                    ->where('stock_quantity', '<=', 0);
            } elseif ($status === 'normal') {
                $query->where('track_stock', true)
                    ->where('stock_quantity', '>', 0)
                    ->whereRaw('stock_quantity > min_stock');
            } elseif ($status === 'not_tracked') {
                $query->where('track_stock', false);
            }
        });
    }

    // Existing scopes - updated for branch system
    public function scopeLowStock($query)
    {
        return $query->where('track_stock', true)
                    ->whereRaw('stock_quantity <= min_stock')
                    ->where('stock_quantity', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('track_stock', true)
                    ->where('stock_quantity', '<=', 0);
    }

    public function scopeNeedReorder($query)
    {
        return $query->where('track_stock', true)
                    ->whereRaw('stock_quantity <= min_stock');
    }

    // New methods for branch system
    public function scopeInBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function getBranchNameAttribute()
    {
        return $this->branch ? $this->branch->name : 'No Branch';
    }

    // Stock management methods for branch system
    public function deductQuantity($quantity)
    {
        if ($this->track_stock) {
            $this->decrement('stock_quantity', $quantity);
        }
    }

    public function addQuantity($quantity)
    {
        if ($this->track_stock) {
            $this->increment('stock_quantity', $quantity);
        }
    }

    public function hasSufficientStock($quantity)
    {
        if (!$this->track_stock) {
            return true; // If not tracking stock, assume sufficient
        }
        return $this->stock_quantity >= $quantity;
    }

    public function hasSufficientStockInBranch($quantity, $branchId = null)
    {
        if (!$this->track_stock) {
            return true;
        }

        if ($branchId) {
            return $this->where('id', $this->id)
                      ->where('branch_id', $branchId)
                      ->where('stock_quantity', '>=', $quantity)
                      ->exists();
        }
        
        return $this->stock_quantity >= $quantity;
    }

    // Method to get reorder level (alias for min_stock for compatibility)
    public function getReorderLevelAttribute()
    {
        return $this->min_stock;
    }

    // Method to get quantity (alias for stock_quantity for compatibility)
    public function getQuantityAttribute()
    {
        return $this->stock_quantity;
    }

    // Method to get price (alias for selling_price for compatibility)
    public function getPriceAttribute()
    {
        return $this->selling_price;
    }

    // Image URL accessor
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/products/' . $this->image);
        }
        return asset('images/default-product.jpg');
    }

    // Check if product needs reorder
    public function getNeedsReorderAttribute()
    {
        return $this->track_stock && $this->stock_quantity <= $this->min_stock;
    }

    // Calculate suggested reorder quantity
    public function getSuggestedReorderQuantityAttribute()
    {
        if (!$this->track_stock || !$this->max_stock) {
            return 0;
        }
        
        $suggested = $this->max_stock - $this->stock_quantity;
        return max(0, $suggested);
    }

    // Add to Product model relationships
public function productWarranties()
{
    return $this->hasMany(ProductWarranty::class);
}

public function activeWarranties()
{
    return $this->hasMany(ProductWarranty::class)->active();
}

// Check if product has any warranties
public function getHasWarrantyAttribute()
{
    return $this->productWarranties()->active()->exists();
}

// Get default warranty (first active warranty)
public function getDefaultWarrantyAttribute()
{
    return $this->activeWarranties()->first();
}
}