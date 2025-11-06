<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\StockAdjustment;
use App\Models\Branch;
use App\Models\Category;
use App\Models\User;
use App\Models\CustomerCredit;
use App\Models\CustomerLoyalty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function dashboard()
    {
        $today = now()->format('Y-m-d');
        $weekStart = now()->startOfWeek()->format('Y-m-d');
        $monthStart = now()->startOfMonth()->format('Y-m-d');

        // Today's stats
        $todayStats = $this->getSalesStats($today, $today);
        
        // Week-to-date stats
        $weekStats = $this->getSalesStats($weekStart, $today);
        
        // Month-to-date stats
        $monthStats = $this->getSalesStats($monthStart, $today);

        // Inventory alerts
        $inventoryAlerts = $this->getInventoryAlerts();

        // Top selling products this month
        $topProducts = $this->getTopProducts($monthStart, $today, 5);

        // Sales trend (last 7 days)
        $salesTrend = $this->getSalesTrend(7);

        // Payment method breakdown
        $paymentBreakdown = $this->getPaymentBreakdown($monthStart, $today);

        // Recent activities
        $recentActivities = $this->getRecentActivities();

        return view('reports.dashboard', compact(
            'todayStats', 
            'weekStats', 
            'monthStats', 
            'topProducts',
            'salesTrend',
            'paymentBreakdown',
            'inventoryAlerts',
            'recentActivities'
        ));
    }

    // SALES REPORTS
    public function salesSummary(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $salesData = $this->getSalesStats($startDate, $endDate);
        $dailySales = $this->getDailySales($startDate, $endDate);
        $topProducts = $this->getTopProducts($startDate, $endDate, 10);
        $salesByCategory = $this->getSalesByCategory($startDate, $endDate);
        $salesByHour = $this->getSalesByHour($startDate, $endDate);

        return view('reports.sales.summary', compact(
            'salesData', 
            'dailySales', 
            'topProducts', 
            'salesByCategory',
            'salesByHour',
            'startDate', 
            'endDate'
        ));
    }

    public function detailedSales(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $sales = Sale::with(['customer', 'items.product', 'payments'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $summary = $this->getSalesStats($startDate, $endDate);

        return view('reports.sales.detailed', compact('sales', 'summary', 'startDate', 'endDate'));
    }

    public function productPerformance(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $topProducts = $this->getTopProducts($startDate, $endDate, 20);
        $slowMovingProducts = $this->getSlowMovingProducts($startDate, $endDate);
        $productCategories = $this->getProductCategoryPerformance($startDate, $endDate);

        return view('reports.sales.product-performance', compact(
            'topProducts', 
            'slowMovingProducts',
            'productCategories',
            'startDate', 
            'endDate'
        ));
    }

    public function taxReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $taxData = Sale::whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->selectRaw('
                DATE(created_at) as date,
                SUM(tax_amount) as daily_tax,
                COUNT(*) as invoice_count,
                SUM(total_amount) as total_sales
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalTax = $taxData->sum('daily_tax');
        $totalSales = $taxData->sum('total_sales');
        $taxRate = $totalSales > 0 ? ($totalTax / $totalSales) * 100 : 0;

        return view('reports.sales.tax-report', compact('taxData', 'totalTax', 'taxRate', 'startDate', 'endDate'));
    }

    // INVENTORY REPORTS
    public function stockLevels(Request $request)
    {
        $lowStockThreshold = $request->get('low_stock_threshold', 10);
        
        $products = Product::with(['category', 'branch'])
            ->where('track_stock', true)
            ->get()
            ->map(function($product) {
                $product->stock_value = $product->stock_quantity * $product->purchase_price;
                $product->reorder_status = $this->getReorderStatus($product);
                return $product;
            });

        $stockSummary = [
            'total_products' => $products->count(),
            'total_stock_value' => $products->sum('stock_value'),
            'low_stock_count' => $products->where('reorder_status', 'low_stock')->count(),
            'out_of_stock_count' => $products->where('reorder_status', 'out_of_stock')->count(),
            'normal_stock_count' => $products->where('reorder_status', 'normal')->count(),
        ];

        return view('reports.inventory.stock-levels', compact('products', 'stockSummary', 'lowStockThreshold'));
    }

    public function movementReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $movements = StockMovement::with(['product', 'user'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $movementSummary = $this->getMovementSummary($startDate, $endDate);

        return view('reports.inventory.movement-report', compact('movements', 'movementSummary', 'startDate', 'endDate'));
    }

    public function slowMoving(Request $request)
    {
        $daysThreshold = $request->get('days_threshold', 30);
        
        $slowMovingProducts = Product::with(['category'])
            ->where('track_stock', true)
            ->get()
            ->filter(function($product) use ($daysThreshold) {
                $lastSale = DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->where('sale_items.product_id', $product->id)
                    ->where('sales.created_at', '>=', now()->subDays($daysThreshold))
                    ->orderBy('sales.created_at', 'desc')
                    ->first();

                return !$lastSale && $product->stock_quantity > 0;
            })
            ->values();

        return view('reports.inventory.slow-moving', compact('slowMovingProducts', 'daysThreshold'));
    }

    public function stockValuation()
    {
        $valuation = Product::where('track_stock', true)
            ->selectRaw('
                SUM(stock_quantity * purchase_price) as total_cost_value,
                SUM(stock_quantity * selling_price) as total_retail_value,
                COUNT(*) as total_products,
                SUM(stock_quantity) as total_units
            ')
            ->first();

        $productsByCategory = Product::with('category')
            ->where('track_stock', true)
            ->get()
            ->groupBy('category.name')
            ->map(function($categoryProducts) {
                return [
                    'cost_value' => $categoryProducts->sum(function($p) { return $p->stock_quantity * $p->purchase_price; }),
                    'retail_value' => $categoryProducts->sum(function($p) { return $p->stock_quantity * $p->selling_price; }),
                    'product_count' => $categoryProducts->count(),
                    'unit_count' => $categoryProducts->sum('stock_quantity')
                ];
            });

        return view('reports.inventory.stock-valuation', compact('valuation', 'productsByCategory'));
    }

    // CUSTOMER REPORTS
    public function customerSpending(Request $request)
    {
        $startDate = $request->get('start_date', now()->subYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $customers = Customer::withCount(['sales as total_orders' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }])
            ->withSum(['sales as total_spent' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }], 'total_amount')
            ->having('total_orders', '>', 0)
            ->orderByDesc('total_spent')
            ->paginate(20);

        return view('reports.customers.spending', compact('customers', 'startDate', 'endDate'));
    }

    public function customerFrequency(Request $request)
    {
        $customers = Customer::withCount('sales')
            ->with(['sales' => function($query) {
                $query->select('customer_id', DB::raw('COUNT(*) as purchase_count'))
                    ->groupBy('customer_id');
            }])
            ->having('sales_count', '>', 0)
            ->orderByDesc('sales_count')
            ->paginate(20);

        $frequencyAnalysis = [
            'one_time' => Customer::has('sales', '=', 1)->count(),
            'regular' => Customer::has('sales', '<=', 5)->has('sales', '>', 1)->count(),
            'frequent' => Customer::has('sales', '>', 5)->count(),
        ];

        return view('reports.customers.frequency', compact('customers', 'frequencyAnalysis'));
    }

    public function loyaltyReport()
    {
        $loyaltyData = Customer::withSum('sales', 'total_amount')
            ->withCount('sales')
            ->where('loyalty_points', '>', 0)
            ->orderByDesc('loyalty_points')
            ->get();

        $loyaltySummary = [
            'total_points' => $loyaltyData->sum('loyalty_points'),
            'active_members' => $loyaltyData->count(),
            'average_points' => $loyaltyData->avg('loyalty_points'),
            'top_member' => $loyaltyData->first(),
        ];

        return view('reports.customers.loyalty', compact('loyaltyData', 'loyaltySummary'));
    }

    public function customerAcquisition(Request $request)
    {
        $startDate = $request->get('start_date', now()->subYear()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $acquisitionData = Customer::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('
                DATE(created_at) as acquisition_date,
                COUNT(*) as new_customers
            ')
            ->groupBy('acquisition_date')
            ->orderBy('acquisition_date')
            ->get();

        $sourceAnalysis = Customer::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('
                COALESCE(source, "Unknown") as source,
                COUNT(*) as customer_count
            ')
            ->groupBy('source')
            ->get();

        return view('reports.customers.acquisition', compact('acquisitionData', 'sourceAnalysis', 'startDate', 'endDate'));
    }

    // FINANCIAL REPORTS
    public function profitLoss(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $revenue = Sale::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('total_amount');

        $cogs = $this->getCostOfGoodsSold($startDate, $endDate);
        $expenses = $this->getOperatingExpenses($startDate, $endDate);
        
        $grossProfit = $revenue - $cogs;
        $netProfit = $grossProfit - $expenses;

        $profitMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        return view('reports.financial.profit-loss', compact(
            'revenue', 'cogs', 'expenses', 'grossProfit', 'netProfit', 'profitMargin', 'startDate', 'endDate'
        ));
    }

    // HELPER METHODS
    private function getSalesStats($startDate, $endDate)
    {
        return Sale::whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                SUM(tax_amount) as total_tax,
                SUM(discount_amount) as total_discount,
                AVG(total_amount) as average_sale,
                MIN(total_amount) as min_sale,
                MAX(total_amount) as max_sale
            ')
            ->first();
    }

    private function getTopProducts($startDate, $endDate, $limit = 5)
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('
                products.name,
                products.sku,
                products.purchase_price,
                SUM(sale_items.quantity) as total_quantity,
                SUM(sale_items.total_price) as total_revenue,
                SUM((sale_items.unit_price - products.purchase_price) * sale_items.quantity) as total_profit
            ')
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.purchase_price')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }

    private function getSalesTrend($days = 7)
    {
        $startDate = now()->subDays($days - 1)->startOfDay();
        $endDate = now()->endOfDay();

        return Sale::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as sales_count,
                SUM(total_amount) as total_revenue,
                SUM(tax_amount) as total_tax,
                AVG(total_amount) as average_sale
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getPaymentBreakdown($startDate, $endDate)
    {
        return Sale::whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->selectRaw('
                payment_method,
                COUNT(*) as transaction_count,
                SUM(total_amount) as total_amount,
                (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM sales WHERE DATE(created_at) BETWEEN ? AND ?)) as percentage
            ', [$startDate, $endDate])
            ->groupBy('payment_method')
            ->get();
    }

    private function getInventoryAlerts()
    {
        return Product::where('track_stock', true)
            ->where(function($query) {
                $query->where('stock_quantity', '<=', DB::raw('min_stock'))
                    ->orWhere('stock_quantity', '=', 0);
            })
            ->with('category')
            ->orderBy('stock_quantity')
            ->limit(10)
            ->get();
    }

    private function getRecentActivities()
    {
        // Combine recent sales, stock movements, etc.
        $recentSales = Sale::with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentMovements = StockMovement::with('product')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'sales' => $recentSales,
            'movements' => $recentMovements
        ];
    }

    private function getReorderStatus($product)
    {
        if ($product->stock_quantity == 0) {
            return 'out_of_stock';
        } elseif ($product->stock_quantity <= $product->min_stock) {
            return 'low_stock';
        } else {
            return 'normal';
        }
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sales,products,customers,inventory,financial',
            'format' => 'required|in:csv,pdf',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Generate report based on type
        switch ($request->type) {
            case 'sales':
                return $this->exportSalesReport($request);
            case 'inventory':
                return $this->exportInventoryReport($request);
            case 'customers':
                return $this->exportCustomerReport($request);
            case 'financial':
                return $this->exportFinancialReport($request);
            default:
                return back()->with('error', 'Invalid report type');
        }
    }

    // Add export methods here...
}