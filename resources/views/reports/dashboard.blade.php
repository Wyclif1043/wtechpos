@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h1>
        <p class="text-gray-600">Real-time business performance overview</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Today's Revenue -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Today's Revenue</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($todayStats->total_revenue ?? 0, 2) }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-dollar-sign text-blue-600 text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ $todayStats->total_sales ?? 0 }} sales today</p>
        </div>

        <!-- Week's Revenue -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">This Week</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($weekStats->total_revenue ?? 0, 2) }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-chart-line text-green-600 text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ $weekStats->total_sales ?? 0 }} sales this week</p>
        </div>

        <!-- Month's Revenue -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">This Month</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($monthStats->total_revenue ?? 0, 2) }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ $monthStats->total_sales ?? 0 }} sales this month</p>
        </div>

        <!-- Inventory Alerts -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Stock Alerts</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $inventoryAlerts->count() }}</p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Products need attention</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Sales Trend Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sales Trend (Last 7 Days)</h3>
            <div class="h-64">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Payment Method Breakdown -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Methods</h3>
            <div class="h-64">
                <canvas id="paymentMethodChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Products -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Selling Products</h3>
            <div class="space-y-4">
                @forelse($topProducts as $product)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $product->name }}</p>
                        <p class="text-sm text-gray-600">{{ $product->total_quantity }} sold</p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-green-600">${{ number_format($product->total_revenue, 2) }}</p>
                        <p class="text-sm text-gray-600">${{ number_format($product->total_profit, 2) }} profit</p>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">No sales data available</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
            <div class="space-y-4">
                @foreach($recentActivities['sales'] as $sale)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">Sale #{{ $sale->sale_number }}</p>
                        <p class="text-sm text-gray-600">{{ $sale->customer->name ?? 'Walk-in' }} • ${{ number_format($sale->total_amount, 2) }}</p>
                    </div>
                    <span class="text-sm text-gray-500">{{ $sale->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
                
                @foreach($recentActivities['movements'] as $movement)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">Stock Movement</p>
                        <p class="text-sm text-gray-600">{{ $movement->product->name }} • {{ $movement->quantity }} units</p>
                    </div>
                    <span class="text-sm text-gray-500">{{ $movement->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Inventory Alerts Section -->
    @if($inventoryAlerts->count() > 0)
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Inventory Alerts</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($inventoryAlerts as $product)
            <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                <div class="flex items-center justify-between mb-2">
                    <p class="font-medium text-gray-900">{{ $product->name }}</p>
                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                        {{ $product->stock_quantity == 0 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $product->stock_quantity == 0 ? 'Out of Stock' : 'Low Stock' }}
                    </span>
                </div>
                <p class="text-sm text-gray-600">Current: {{ $product->stock_quantity }} • Min: {{ $product->min_stock }}</p>
                <p class="text-sm text-gray-600">Category: {{ $product->category->name ?? 'N/A' }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Trend Chart
    const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
    const salesTrendChart = new Chart(salesTrendCtx, {
        type: 'line',
        data: {
            labels: @json($salesTrend->pluck('date')),
            datasets: [{
                label: 'Daily Revenue',
                data: @json($salesTrend->pluck('total_revenue')),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });

    // Payment Method Chart
    const paymentCtx = document.getElementById('paymentMethodChart').getContext('2d');
    const paymentChart = new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: @json($paymentBreakdown->pluck('payment_method')),
            datasets: [{
                data: @json($paymentBreakdown->pluck('total_amount')),
                backgroundColor: [
                    '#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush
@endsection