<?php
// app/Http/Controllers/SalesReportController.php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesReportController extends Controller
{
    public function salesSummary()
    {
        $dateRange = $this->getDateRange(request('period', 'today'));

        $summary = $this->getSalesSummary($dateRange['start'], $dateRange['end']);
        $hourlyData = $this->getHourlySales($dateRange['start'], $dateRange['end']);
        $categorySales = $this->getCategorySales($dateRange['start'], $dateRange['end']);
        $paymentMethods = $this->getPaymentMethodSummary($dateRange['start'], $dateRange['end']);

        return view('reports.sales.summary', compact(
            'summary', 
            'hourlyData', 
            'categorySales', 
            'paymentMethods',
            'dateRange'
        ));
    }

    public function detailedSales()
    {
        $dateRange = $this->getDateRange(request('period', 'today'));

        $sales = Sale::with(['customer', 'user', 'items.product'])
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('reports.sales.detailed', compact('sales', 'dateRange'));
    }

    public function productPerformance()
    {
        $dateRange = $this->getDateRange(request('period', 'month'));

        $products = Product::with(['category'])
            ->whereHas('saleItems', function($query) use ($dateRange) {
                $query->whereHas('sale', function($q) use ($dateRange) {
                    $q->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
                });
            })
            ->withCount(['saleItems as total_quantity_sold' => function($query) use ($dateRange) {
                $query->select(DB::raw('COALESCE(SUM(quantity), 0)'))
                    ->whereHas('sale', function($q) use ($dateRange) {
                        $q->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
                    });
            }])
            ->withSum(['saleItems as total_revenue' => function($query) use ($dateRange) {
                $query->whereHas('sale', function($q) use ($dateRange) {
                    $q->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
                });
            }], 'total_price')
            ->orderByDesc('total_quantity_sold')
            ->paginate(20);

        // Calculate profit for each product
        $products->each(function($product) use ($dateRange) {
            $cost = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->where('products.id', $product->id)
                ->whereBetween('sales.created_at', [$dateRange['start'], $dateRange['end']])
                ->sum(DB::raw('(sale_items.unit_price - products.purchase_price) * sale_items.quantity'));
            
            $product->total_profit = $cost;
        });

        return view('reports.sales.product-performance', compact('products', 'dateRange'));
    }

    public function taxReport()
    {
        $dateRange = $this->getDateRange(request('period', 'month'));

        $taxSummary = Sale::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('
                DATE(created_at) as date,
                SUM(tax_amount) as total_tax,
                SUM(subtotal) as total_sales,
                COUNT(*) as total_transactions
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalTax = $taxSummary->sum('total_tax');
        $totalSales = $taxSummary->sum('total_sales');

        return view('reports.sales.tax-report', compact('taxSummary', 'totalTax', 'totalSales', 'dateRange'));
    }

    private function getDateRange($period)
    {
        return match($period) {
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
                'label' => 'Today'
            ],
            'yesterday' => [
                'start' => now()->subDay()->startOfDay(),
                'end' => now()->subDay()->endOfDay(),
                'label' => 'Yesterday'
            ],
            'week' => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
                'label' => 'This Week'
            ],
            'month' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
                'label' => 'This Month'
            ],
            'last_month' => [
                'start' => now()->subMonth()->startOfMonth(),
                'end' => now()->subMonth()->endOfMonth(),
                'label' => 'Last Month'
            ],
            'quarter' => [
                'start' => now()->startOfQuarter(),
                'end' => now()->endOfQuarter(),
                'label' => 'This Quarter'
            ],
            'year' => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear(),
                'label' => 'This Year'
            ],
            default => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
                'label' => 'Today'
            ]
        };
    }

    private function getSalesSummary($startDate, $endDate)
    {
        return Sale::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                SUM(tax_amount) as total_tax,
                SUM(discount_amount) as total_discount,
                AVG(total_amount) as average_sale,
                SUM(total_amount) / COUNT(DISTINCT DATE(created_at)) as daily_average,
                MIN(total_amount) as smallest_sale,
                MAX(total_amount) as largest_sale
            ')
            ->first();
    }

    private function getHourlySales($startDate, $endDate)
    {
        return Sale::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                HOUR(created_at) as hour,
                COUNT(*) as sales_count,
                SUM(total_amount) as total_revenue
            ')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    private function getCategorySales($startDate, $endDate)
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->selectRaw('
                categories.name as category_name,
                SUM(sale_items.quantity) as total_quantity,
                SUM(sale_items.total_price) as total_revenue,
                COUNT(DISTINCT sales.id) as sales_count
            ')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get();
    }

    private function getPaymentMethodSummary($startDate, $endDate)
    {
        return Sale::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                payment_method,
                COUNT(*) as transaction_count,
                SUM(total_amount) as total_amount,
                AVG(total_amount) as average_amount
            ')
            ->groupBy('payment_method')
            ->get();
    }
}