@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h1>
                <p class="text-sm text-gray-600">Customer since {{ $customer->created_at->format('M d, Y') }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('customers.index') }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Back to List
                </a>
                <a href="{{ route('customers.edit', $customer) }}" 
                   class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Edit
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Customer Details -->
            <div class="lg:col-span-2">
                <!-- Customer Information -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Customer Information</h3>
                    </div>
                    <div class="border-t border-gray-200">
                        <dl>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $customer->name }}</dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $customer->email ?? 'Not provided' }}
                                </dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $customer->phone ?? 'Not provided' }}
                                </dd>
                            </div>
                            @if($customer->address)
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Address</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $customer->address }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Recent Sales -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Sales</h3>
                        <a href="{{ route('customers.sales-history', $customer) }}" 
                           class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            View All Sales
                        </a>
                    </div>
                    <div class="border-t border-gray-200">
                        @if($customer->sales->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($customer->sales as $sale)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $sale->sale_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $sale->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($sale->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $sale->items->count() }} items
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <div class="px-4 py-8 text-center">
                            <i class="fas fa-shopping-cart text-gray-400 text-3xl mb-2"></i>
                            <p class="text-sm text-gray-600">No sales history</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Customer Stats -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Customer Stats</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Total Sales</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_sales'] }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Total Spent</div>
                            <div class="text-2xl font-bold text-green-600">${{ number_format($stats['total_spent'], 2) }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Average Sale</div>
                            <div class="text-2xl font-bold text-blue-600">${{ number_format($stats['average_sale'], 2) }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Last Purchase</div>
                            <div class="text-lg font-medium text-gray-900">{{ $stats['last_purchase'] }}</div>
                        </div>
                    </div>
                </div>

                <!-- Credit & Loyalty -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Credit & Loyalty</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Credit Balance</div>
                            <div class="text-2xl font-bold {{ $customer->credit_balance > 0 ? 'text-red-600' : 'text-gray-600' }}">
                                ${{ number_format($customer->credit_balance, 2) }}
                            </div>
                            @if($customer->credit_balance > 0)
                            <a href="{{ route('customers.credit.show', $customer) }}" 
                               class="text-sm text-blue-600 hover:text-blue-900">
                                Manage Credit
                            </a>
                            @endif
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Loyalty Points</div>
                            <div class="text-2xl font-bold text-yellow-600">
                                {{ number_format($customer->loyalty_points) }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ $customer->loyalty_tier }} Tier
                                @if($customer->loyalty_discount_rate > 0)
                                • {{ number_format($customer->loyalty_discount_rate * 100) }}% Discount
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 space-y-2">
                        <a href="{{ route('pos.interface') }}?customer_id={{ $customer->id }}" 
                           class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center justify-center">
                            <i class="fas fa-cash-register mr-2"></i>
                            New Sale
                        </a>
                        @if($customer->credit_balance > 0)
                        <a href="{{ route('customers.credit.payment', $customer) }}" 
                           class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center justify-center">
                            <i class="fas fa-credit-card mr-2"></i>
                            Make Payment
                        </a>
                        @endif
                        <a href="{{ route('customers.sales-history', $customer) }}" 
                           class="w-full bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 flex items-center justify-center">
                            <i class="fas fa-history mr-2"></i>
                            Sales History
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection