<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'low_stock_products' => Product::where('stock_quantity', '<=', \DB::raw('min_stock'))->count(),
            'total_customers' => Customer::count(),
            'today_sales' => Sale::whereDate('created_at', today())->count(),
        ];

        return view('dashboard', compact('stats'));
    }
}