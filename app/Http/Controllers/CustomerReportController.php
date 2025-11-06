<?php
// app/Http/Controllers/CustomerReportController.php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerReportController extends Controller
{
    public function customerSpending()
    {
        $customers = Customer::withCount('sales')
            ->withSum('sales as total_spent', 'total_amount')
            ->having('total_spent', '>', 0)
            ->orderByDesc('total_spent')
            ->paginate(50);

        return view('reports.customers.spending', compact('customers'));
    }

    public function customerFrequency()
    {
        $customers = Customer::withCount('sales')
            ->with(['sales' => function($query) {
                $query->select('customer_id', DB::raw('MAX(created_at) as last_purchase'))
                    ->groupBy('customer_id');
            }])
            ->having('sales_count', '>', 0)
            ->orderByDesc('sales_count')
            ->paginate(50);

        return view('reports.customers.frequency', compact('customers'));
    }

    public function customerLoyalty()
    {
        $customers = Customer::where('loyalty_points', '>', 0)
            ->orderByDesc('loyalty_points')
            ->paginate(50);

        $loyaltySummary = [
            'total_customers' => Customer::count(),
            'with_points' => Customer::where('loyalty_points', '>', 0)->count(),
            'gold_tier' => Customer::where('total_spent', '>=', 1000)->count(),
            'silver_tier' => Customer::whereBetween('total_spent', [500, 999.99])->count(),
            'bronze_tier' => Customer::whereBetween('total_spent', [100, 499.99])->count(),
        ];

        return view('reports.customers.loyalty', compact('customers', 'loyaltySummary'));
    }

    public function customerAcquisition()
    {
        $acquisitionData = Customer::selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as new_customers,
                SUM(total_spent) as total_spent
            ')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12);

        return view('reports.customers.acquisition', compact('acquisitionData'));
    }
}