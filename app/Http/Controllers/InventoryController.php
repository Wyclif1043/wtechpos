<?php
// app/Http/Controllers/InventoryController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_products' => Product::count(),
            'low_stock_products' => Product::where('track_stock', true)
                ->whereRaw('stock_quantity <= min_stock')
                ->where('stock_quantity', '>', 0)
                ->count(),
            'out_of_stock_products' => Product::where('track_stock', true)
                ->where('stock_quantity', '<=', 0)
                ->count(),
            'total_stock_value' => Product::sum(DB::raw('stock_quantity * purchase_price')),
        ];

        $lowStockProducts = Product::where('track_stock', true)
            ->whereRaw('stock_quantity <= min_stock')
            ->where('stock_quantity', '>', 0)
            ->with('category', 'supplier')
            ->orderBy('stock_quantity')
            ->limit(10)
            ->get();

        $recentMovements = StockMovement::with(['product', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        return view('inventory.dashboard', compact('stats', 'lowStockProducts', 'recentMovements'));
    }

    public function products(Request $request)
    {
        $query = Product::with(['category', 'supplier']);

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%');
            });
        }

        // Apply category filter
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Apply stock status filter
        if ($request->has('stock_status') && $request->stock_status) {
            switch ($request->stock_status) {
                case 'low':
                    $query->where('track_stock', true)
                          ->whereRaw('stock_quantity <= min_stock')
                          ->where('stock_quantity', '>', 0);
                    break;
                case 'out':
                    $query->where('track_stock', true)
                          ->where('stock_quantity', '<=', 0);
                    break;
                case 'normal':
                    $query->where('track_stock', true)
                          ->whereRaw('stock_quantity > min_stock');
                    break;
            }
        }

        $products = $query->orderBy('name')->paginate(20);
        $categories = \App\Models\Category::all();

        return view('inventory.products', compact('products', 'categories'));
    }

    public function stockMovements(Request $request)
    {
        $query = StockMovement::with(['product', 'user']);

        // Apply type filter
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Apply product filter
        if ($request->has('product') && $request->product) {
            $query->where('product_id', $request->product);
        }

        // Apply date filters
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->latest()->paginate(50);
        $products = Product::where('is_active', true)->get();

        return view('inventory.stock-movements', compact('movements', 'products'));
    }

    // In LowStockReport method
    public function lowStockReport()
    {
        $products = Product::where('track_stock', true)
            ->whereRaw('stock_quantity <= min_stock')
            ->with(['category' => function($query) {
                $query->withDefault(['name' => 'No Category']);
            }, 'supplier' => function($query) {
                $query->withDefault(['name' => 'No Supplier']);
            }])
            ->orderBy('stock_quantity')
            ->paginate(50);

        return view('inventory.low-stock-report', compact('products'));
    }

    // In StockValuation method
    public function stockValuation()
    {
        $products = Product::with(['category' => function($query) {
                $query->withDefault(['name' => 'No Category']);
            }])
            ->where('track_stock', true)
            ->orderBy(DB::raw('stock_quantity * purchase_price'), 'desc')
            ->paginate(50);

        $totalValue = Product::where('track_stock', true)
            ->sum(DB::raw('stock_quantity * purchase_price'));

        return view('inventory.stock-valuation', compact('products', 'totalValue'));
    }
}