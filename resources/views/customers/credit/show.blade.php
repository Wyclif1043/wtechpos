@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Credit History: {{ $customer->name }}</h1>
                <p class="text-sm text-gray-600">Credit balance: 
                    <span class="font-bold text-red-600">${{ number_format($customer->credit_balance, 2) }}</span>
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('customer-credit.index') }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Back to Credit List
                </a>
                <a href="{{ route('customers.show', $customer) }}" 
                   class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    View Customer
                </a>
                <a href="{{ route('customers.credit.payment', $customer) }}" 
                   class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Record Payment
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Credit Transactions -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Credit Transactions</h3>
                        <p class="mt-1 text-sm text-gray-600">All credit-related transactions for this customer.</p>
                    </div>
                    <div class="border-t border-gray-200">
                        @if($transactions->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($transactions as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $transaction->created_at->format('M d, Y') }}
                                        <div class="text-xs text-gray-500">{{ $transaction->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($transaction->type === 'credit_sale')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Credit Sale
                                            </span>
                                        @elseif($transaction->type === 'payment')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Payment
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                Adjustment
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($transaction->amount > 0)
                                            <span class="text-red-600 font-medium">+${{ number_format($transaction->amount, 2) }}</span>
                                        @else
                                            <span class="text-green-600 font-medium">${{ number_format($transaction->amount, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($transaction->new_balance, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $transaction->notes }}
                                        @if($transaction->reference_type && $transaction->reference_id)
                                        <div class="text-xs text-gray-500">
                                            Ref: {{ class_basename($transaction->reference_type) }} #{{ $transaction->reference_id }}
                                        </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $transaction->user->name ?? 'System' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <div class="px-4 py-8 text-center">
                            <i class="fas fa-receipt text-gray-400 text-4xl mb-2"></i>
                            <p class="text-sm text-gray-600">No credit transactions found</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Customer Summary -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Customer Summary</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Current Credit Balance</div>
                            <div class="text-2xl font-bold text-red-600">${{ number_format($customer->credit_balance, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Total Sales</div>
                            <div class="text-lg font-medium text-gray-900">{{ $customer->sales_count ?? 0 }} sales</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Total Amount Spent</div>
                            <div class="text-lg font-medium text-gray-900">${{ number_format($customer->total_spent, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Loyalty Tier</div>
                            <div class="text-lg font-medium text-gray-900">{{ $customer->loyalty_tier }}</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 space-y-2">
                        <a href="{{ route('customers.credit.payment', $customer) }}" 
                           class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center justify-center">
                            <i class="fas fa-credit-card mr-2"></i>
                            Record Payment
                        </a>
                        <a href="{{ route('pos.interface') }}?customer_id={{ $customer->id }}" 
                           class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center justify-center">
                            <i class="fas fa-cash-register mr-2"></i>
                            New Sale
                        </a>
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