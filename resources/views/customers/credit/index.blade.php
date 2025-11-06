@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Customer Credit Management</h1>
            <a href="{{ route('customers.index') }}" 
               class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Back to Customers
            </a>
        </div>

        <!-- Credit Summary -->
        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-lg p-6 mb-6">
            <div class="text-center text-white">
                <h2 class="text-2xl font-bold mb-2">Total Outstanding Credit</h2>
                <div class="text-4xl font-bold mb-2">${{ number_format($totalCredit, 2) }}</div>
                <p class="text-red-100">Across {{ $customers->total() }} customers with credit balances</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Search Customers</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Name, email, or phone"
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Min Credit Balance</label>
                    <input type="number" name="min_balance" value="{{ request('min_balance') }}" step="0.01" min="0"
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Filter
                    </button>
                    <a href="{{ route('customer-credit.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Customers with Credit Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Purchase</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($customers as $customer)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-red-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-semibold text-sm">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <a href="{{ route('customers.show', $customer) }}" class="hover:text-blue-600">
                                            {{ $customer->name }}
                                        </a>
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $customer->loyalty_tier }} Tier</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div>{{ $customer->email ?? 'No email' }}</div>
                            <div>{{ $customer->phone ?? 'No phone' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-lg font-bold text-red-600">
                                ${{ number_format($customer->credit_balance, 2) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>{{ $customer->sales_count ?? 0 }} sales</div>
                            <div class="text-gray-500">${{ number_format($customer->total_spent, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($customer->last_purchase)
                                {{ \Carbon\Carbon::parse($customer->last_purchase)->format('M d, Y') }}
                            @else
                                <span class="text-gray-400">Never</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="{{ route('customers.credit.show', $customer) }}" 
                               class="text-blue-600 hover:text-blue-900">
                                View
                            </a>
                            <a href="{{ route('customers.credit.payment', $customer) }}" 
                               class="text-green-600 hover:text-green-900">
                                Payment
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $customers->links() }}
        </div>

        <!-- Empty State -->
        @if($customers->isEmpty())
        <div class="text-center py-12">
            <i class="fas fa-credit-card text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Customers with Credit</h3>
            <p class="text-gray-500 mb-4">All customers have zero credit balance.</p>
            <a href="{{ route('customers.index') }}" class="text-blue-600 hover:text-blue-900">
                View all customers →
            </a>
        </div>
        @endif
    </div>
</div>
@endsection