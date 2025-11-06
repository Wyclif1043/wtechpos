@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Sale Details</h1>
                    <p class="text-gray-600 mt-2">Sale #{{ $sale->sale_number }}</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="window.print()" 
                            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                        <i class="fas fa-print mr-2"></i>Print Receipt
                    </button>
                    <a href="{{ route('sales.index') }}" 
                       class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Sales
                    </a>
                </div>
            </div>
        </div>

        <!-- Sale Information -->
        <div class="bg-white rounded-lg shadow-sm border mb-6">
            <div class="px-6 py-5 border-b">
                <h2 class="text-lg font-semibold">Sale Information</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Basic Information</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Sale Number:</span>
                                <span class="font-medium">{{ $sale->sale_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Customer:</span>
                                <span class="font-medium">{{ $sale->customer->name ?? 'Walk-in Customer' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date & Time:</span>
                                <span class="font-medium">{{ $sale->created_at->format('M j, Y g:i A') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Sold by:</span>
                                <span class="font-medium">{{ $sale->user->name ?? 'System' }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Payment Summary</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-medium">${{ number_format($sale->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax (16%):</span>
                                <span class="font-medium">${{ number_format($sale->tax_amount, 2) }}</span>
                            </div>
                            @if($sale->discount_amount > 0)
                            <div class="flex justify-between text-red-600">
                                <span class="text-gray-600">Discount:</span>
                                <span class="font-medium">-${{ number_format($sale->discount_amount, 2) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between border-t pt-2">
                                <span class="text-gray-800 font-semibold">Total Amount:</span>
                                <span class="text-green-600 font-bold">${{ number_format($sale->total_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Paid:</span>
                                <span class="font-medium text-green-600">${{ number_format($sale->total_paid, 2) }}</span>
                            </div>
                            @if($sale->balance_due > 0)
                            <div class="flex justify-between text-red-600">
                                <span class="text-gray-600">Balance Due:</span>
                                <span class="font-medium">${{ number_format($sale->balance_due, 2) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                @php
                                    $statusColors = [
                                        'paid' => 'bg-green-100 text-green-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'partial' => 'bg-blue-100 text-blue-800'
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$sale->payment_status] ?? 'bg-gray-100 text-gray-800' }} capitalize">
                                    {{ $sale->payment_status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($sale->notes)
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Notes</h3>
                    <p class="text-gray-700 bg-gray-50 p-3 rounded">{{ $sale->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white rounded-lg shadow-sm border mb-6">
            <div class="px-6 py-5 border-b">
                <h2 class="text-lg font-semibold">Items Sold</h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Price
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($sale->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                    <div class="text-sm text-gray-500">SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">${{ number_format($item->unit_price, 2) }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $item->quantity }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">${{ number_format($item->total_price, 2) }}</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-right text-sm font-medium text-gray-900">
                                    Subtotal:
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    ${{ number_format($sale->subtotal, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payments Table -->
        @if($sale->payments->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-6 py-5 border-b">
                <h2 class="text-lg font-semibold">Payments</h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Method
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Reference
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date & Time
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Processed By
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($sale->payments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 capitalize">
                                        {{ str_replace('_', ' ', $payment->payment_method) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">${{ number_format($payment->amount, 2) }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $payment->reference ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $payment->created_at->format('M j, Y g:i A') }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $payment->user->name ?? 'System' }}</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="4" class="px-4 py-4 text-right text-sm font-medium text-gray-900">
                                    Total Paid:
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                    ${{ number_format($sale->total_paid, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
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
}
</style>
@endsection