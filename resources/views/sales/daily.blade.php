@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Daily Sales Report</h1>
            <p class="text-gray-600 mt-2">View daily sales performance and metrics</p>
        </div>

        <!-- Date Selector Form -->
        <form method="GET" action="{{ route('sales.reports.daily') }}" class="bg-white rounded-lg shadow-sm border p-4 mb-6 no-print">
            <div class="flex flex-col md:flex-row md:items-end md:space-x-4 space-y-4 md:space-y-0">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Date</label>
                    <input type="date" name="date" value="{{ $report_date ?? date('Y-m-d') }}" 
                           class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <button type="submit" 
                            class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 w-full md:w-auto">
                        <i class="fas fa-chart-bar mr-2"></i>Generate Report
                    </button>
                </div>
                <div>
                    <button type="button" onclick="window.print()" 
                            class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600 w-full md:w-auto">
                        <i class="fas fa-print mr-2"></i>Print Report
                    </button>
                </div>
            </div>
        </form>

        @if(isset($summary) && isset($sales))
        <!-- Report Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-blue-500 rounded-md p-3">
                            <i class="fas fa-shopping-cart text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Sales</p>
                        <p class="text-2xl font-semibold text-gray-900">${{ number_format($summary['total_sales'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-green-500 rounded-md p-3">
                            <i class="fas fa-receipt text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Transactions</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $summary['transaction_count'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-purple-500 rounded-md p-3">
                            <i class="fas fa-chart-line text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Average Sale</p>
                        <p class="text-2xl font-semibold text-gray-900">${{ number_format($summary['average_sale'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-yellow-500 rounded-md p-3">
                            <i class="fas fa-landmark text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Tax Collected</p>
                        <p class="text-2xl font-semibold text-gray-900">${{ number_format($summary['total_tax'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods Breakdown -->
        @if(isset($summary['payment_methods']) && count($summary['payment_methods']) > 0)
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">Payment Methods</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($summary['payment_methods'] as $method => $amount)
                <div class="text-center">
                    <p class="text-sm text-gray-500 capitalize">{{ str_replace('_', ' ', $method) }}</p>
                    <p class="text-lg font-semibold">${{ number_format($amount, 2) }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Sales Details -->
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-6 py-5 border-b">
                <h3 class="text-lg font-semibold">Sales Details - {{ \Carbon\Carbon::parse($report_date)->format('F j, Y') }}</h3>
            </div>
            <div class="p-6">
                @if($sales->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sale Number
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Time
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Items
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Payment Method
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($sales as $sale)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $sale->sale_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $sale->created_at->format('g:i A') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $sale->customer->name ?? 'Walk-in Customer' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $sale->items->count() }} items</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">${{ number_format($sale->total_amount, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $paymentMethod = $sale->payments->first()->payment_method ?? 'multiple';
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 capitalize">
                                        {{ str_replace('_', ' ', $paymentMethod) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8">
                    <i class="fas fa-receipt text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-600">No sales found for this date.</p>
                </div>
                @endif
            </div>
        </div>
        @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <i class="fas fa-chart-bar text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600">Select a date and generate report to view daily sales</p>
        </div>
        @endif
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        font-size: 12pt;
        background: white !important;
    }
    
    .bg-gray-50 {
        background-color: #f9fafb !important;
    }
    
    .shadow-sm {
        box-shadow: none !important;
    }
    
    .border {
        border: 1px solid #e5e7eb !important;
    }
    
    .py-6 {
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
    }
}
</style>
@endsection