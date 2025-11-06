@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Loyalty Program</h1>
            <a href="{{ route('customers.index') }}" 
               class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Back to Customers
            </a>
        </div>

        <!-- Loyalty Summary -->
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 mb-6">
            <div class="text-center text-white">
                <h2 class="text-2xl font-bold mb-2">Total Loyalty Points</h2>
                <div class="text-4xl font-bold mb-2">{{ number_format($totalPoints) }} pts</div>
                <p class="text-yellow-100">Across {{ $customers->total() }} customers with loyalty points</p>
            </div>
        </div>

       <!-- Loyalty Tiers Info -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow text-center">
                <div class="text-2xl font-bold text-gray-600">{{ $tierCounts['standard'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Standard Tier</div>
                <div class="text-xs text-gray-500">0% discount</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow text-center border-2 border-amber-600">
                <div class="text-2xl font-bold text-amber-600">{{ $tierCounts['bronze'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Bronze Tier</div>
                <div class="text-xs text-gray-500">2% discount</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow text-center border-2 border-gray-400">
                <div class="text-2xl font-bold text-gray-600">{{ $tierCounts['silver'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Silver Tier</div>
                <div class="text-xs text-gray-500">5% discount</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow text-center border-2 border-yellow-400">
                <div class="text-2xl font-bold text-yellow-600">{{ $tierCounts['gold'] ?? 0 }}</div>
                <div class="text-sm text-gray-600">Gold Tier</div>
                <div class="text-xs text-gray-500">10% discount</div>
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
                    <label class="block text-sm font-medium text-gray-700">Min Points</label>
                    <input type="number" name="min_points" value="{{ request('min_points') }}" min="0"
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Filter
                    </button>
                    <a href="{{ route('customer-loyalty.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Customers with Loyalty Points Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loyalty Points</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loyalty Tier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($customers as $customer)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-yellow-500 rounded-full flex items-center justify-center">
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
                                    <div class="text-sm text-gray-500">{{ $customer->sales_count ?? 0 }} sales</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div>{{ $customer->email ?? 'No email' }}</div>
                            <div>{{ $customer->phone ?? 'No phone' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-lg font-bold text-yellow-600">
                                {{ number_format($customer->loyalty_points) }} pts
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($customer->loyalty_tier === 'Gold')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-400">
                                    Gold
                                </span>
                            @elseif($customer->loyalty_tier === 'Silver')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 border border-gray-400">
                                    Silver
                                </span>
                            @elseif($customer->loyalty_tier === 'Bronze')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800 border border-amber-400">
                                    Bronze
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Standard
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($customer->loyalty_discount_rate * 100) }}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${{ number_format($customer->total_spent, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="{{ route('customers.loyalty.show', $customer) }}" 
                               class="text-blue-600 hover:text-blue-900">
                                View
                            </a>
                            @if($customer->loyalty_points > 0)
                            <a href="{{ route('customers.loyalty.show', $customer) }}#redeem" 
                               class="text-green-600 hover:text-green-900">
                                Redeem
                            </a>
                            @endif
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
            <i class="fas fa-star text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Customers with Loyalty Points</h3>
            <p class="text-gray-500 mb-4">No customers have earned loyalty points yet.</p>
            <a href="{{ route('customers.index') }}" class="text-blue-600 hover:text-blue-900">
                View all customers →
            </a>
        </div>
        @endif
    </div>
</div>
@endsection