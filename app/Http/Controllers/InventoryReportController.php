<?php
// app/Http/Controllers/InventoryReportController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReportController extends Controller
{
    public function stockLevels()
    {
        $products = Product::with(['category', 'supplier'])
            ->filter(request(['stock_status', 'category', 'supplier']))
            ->orderBy('stock_quantity')
            ->paginate(50);

        $stockSummary = [
            'total_products' => Product::count(),
            'in_stock' => Product::where('stock_quantity', '>', 0)->count(),
            'low_stock' => Product::whereRaw('stock_quantity <= min_stock AND stock_quantity > 0')->count(),
            'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
            'total_value' => Product::sum(DB::raw('stock_quantity * purchase_price')),
        ];

        return view('reports.inventory.stock-levels', compact('products', 'stockSummary'));
    }

    public function movementReport()
    {
        $movements = StockMovement::with(['product', 'user'])
            ->filter(request(['type', 'product', 'date_from', 'date_to']))
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $movementSummary = StockMovement::filter(request(['type', 'product', 'date_from', 'date_to']))
            ->selectRaw('
                type,
                COUNT(*) as transaction_count,
                SUM(quantity) as total_quantity,
                AVG(unit_cost) as average_cost
            ')
            ->groupBy('type')
            ->get();

        return view('reports.inventory.movement-report', compact('movements', 'movementSummary'));
    }

    public function slowMoving()
    {
        $daysThreshold = request('days', 30);
        $thresholdDate = now()->subDays($daysThreshold);

        $slowMoving = Product::with(['category'])
            ->whereDoesntHave('saleItems', function($query) use ($thresholdDate) {
                $query->whereHas('sale', function($q) use ($thresholdDate) {
                    $q->where('created_at', '>=', $thresholdDate);
                });
            })
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity', 'desc')
            ->paginate(50);

        return view('reports.inventory.slow-moving', compact('slowMoving', 'daysThreshold'));
    }

    public function stockValuation()
    {
        $valuation = Product::with(['category'])
            ->selectRaw('
                *,
                (stock_quantity * purchase_price) as stock_value,
                (stock_quantity * (selling_price - purchase_price)) as potential_profit
            ')
            ->orderByDesc('stock_value')
            ->paginate(50);

        $totalValue = $valuation->sum('stock_value');
        $totalPotentialProfit = $valuation->sum('potential_profit');

        return view('reports.inventory.stock-valuation', compact('valuation', 'totalValue', 'totalPotentialProfit'));
    }
}