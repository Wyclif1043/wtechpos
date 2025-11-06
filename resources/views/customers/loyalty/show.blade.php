@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Loyalty History: {{ $customer->name }}</h1>
                <p class="text-sm text-gray-600">Loyalty points: 
                    <span class="font-bold text-yellow-600">{{ number_format($customer->loyalty_points) }} pts</span>
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('customer-loyalty.index') }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Back to Loyalty
                </a>
                <a href="{{ route('customers.show', $customer) }}" 
                   class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    View Customer
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Loyalty Transactions -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Loyalty Transactions</h3>
                        <p class="mt-1 text-sm text-gray-600">All loyalty point transactions for this customer.</p>
                    </div>
                    <div class="border-t border-gray-200">
                        @if($transactions->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
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
                                        @if($transaction->type === 'earn')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Earned
                                            </span>
                                        @elseif($transaction->type === 'redeem')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Redeemed
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                Adjustment
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($transaction->points > 0)
                                            <span class="text-green-600 font-medium">+{{ number_format($transaction->points) }}</span>
                                        @else
                                            <span class="text-red-600 font-medium">{{ number_format($transaction->points) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($transaction->new_points) }} pts
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $transaction->notes }}
                                        @if($transaction->reference_type && $transaction->reference_id)
                                        <div class="text-xs text-gray-500">
                                            Ref: {{ class_basename($transaction->reference_type) }} #{{ $transaction->reference_id }}
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <div class="px-4 py-8 text-center">
                            <i class="fas fa-star text-gray-400 text-4xl mb-2"></i>
                            <p class="text-sm text-gray-600">No loyalty transactions found</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>

                <!-- Redeem Points Section -->
                @if($customer->loyalty_points > 0)
                <div id="redeem" class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Redeem Loyalty Points</h3>
                        <p class="mt-1 text-sm text-gray-600">Redeem points for this customer.</p>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5">
                        <form action="{{ route('customers.loyalty.redeem', $customer) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="points" class="block text-sm font-medium text-gray-700">Points to Redeem *</label>
                                    <input type="number" name="points" id="points" min="1" 
                                           max="{{ $customer->loyalty_points }}" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                                    <p class="mt-1 text-sm text-gray-500">
                                        Available: {{ number_format($customer->loyalty_points) }} pts
                                    </p>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <input type="text" name="notes" id="notes"
                                           placeholder="Reason for redemption..."
                                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" 
                                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Redeem Points
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
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
                            <div class="text-sm font-medium text-gray-500">Current Points</div>
                            <div class="text-2xl font-bold text-yellow-600">{{ number_format($customer->loyalty_points) }} pts</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Loyalty Tier</div>
                            <div class="text-lg font-medium text-gray-900 flex items-center">
                                @if($customer->loyalty_tier === 'Gold')
                                    <i class="fas fa-crown text-yellow-500 mr-2"></i>
                                @elseif($customer->loyalty_tier === 'Silver')
                                    <i class="fas fa-award text-gray-400 mr-2"></i>
                                @elseif($customer->loyalty_tier === 'Bronze')
                                    <i class="fas fa-award text-amber-600 mr-2"></i>
                                @else
                                    <i class="fas fa-user text-blue-500 mr-2"></i>
                                @endif
                                {{ $customer->loyalty_tier }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Discount Rate</div>
                            <div class="text-lg font-medium text-green-600">{{ number_format($customer->loyalty_discount_rate * 100) }}%</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Total Sales</div>
                            <div class="text-lg font-medium text-gray-900">{{ $customer->sales_count ?? 0 }} sales</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Total Amount Spent</div>
                            <div class="text-lg font-medium text-gray-900">${{ number_format($customer->total_spent, 2) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Adjust Points -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Adjust Points</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5">
                        <form action="{{ route('customers.loyalty.adjust', $customer) }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Adjustment Type</label>
                                    <select name="type" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                                        <option value="add">Add Points</option>
                                        <option value="deduct">Deduct Points</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="adjust_points" class="block text-sm font-medium text-gray-700">Points *</label>
                                    <input type="number" name="points" id="adjust_points" min="1" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="reason" class="block text-sm font-medium text-gray-700">Reason *</label>
                                    <input type="text" name="reason" id="reason" required
                                           placeholder="Reason for adjustment..."
                                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="adjust_notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <input type="text" name="notes" id="adjust_notes"
                                           placeholder="Additional notes..."
                                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                                </div>
                                <div>
                                    <button type="submit" 
                                            class="w-full bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                        Adjust Points
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection