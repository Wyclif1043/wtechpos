@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Stock Adjustments</h1>
            <a href="{{ route('stock-adjustments.create') }}" 
               class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 flex items-center">
                <i class="fas fa-plus mr-2"></i>
                New Adjustment
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $adjustments->total() }}</div>
                <div class="text-sm text-gray-600">Total Adjustments</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow text-center">
                <div class="text-2xl font-bold text-green-600">
                    {{ \App\Models\StockAdjustment::where('type', 'add')->count() }}
                </div>
                <div class="text-sm text-gray-600">Stock Additions</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow text-center">
                <div class="text-2xl font-bold text-red-600">
                    {{ \App\Models\StockAdjustment::where('type', 'remove')->count() }}
                </div>
                <div class="text-sm text-gray-600">Stock Removals</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow text-center">
                <div class="text-2xl font-bold text-purple-600">
                    {{ \App\Models\StockAdjustment::whereDate('created_at', today())->count() }}
                </div>
                <div class="text-sm text-gray-600">Today</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="type" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                        <option value="">All Types</option>
                        <option value="add" {{ request('type') == 'add' ? 'selected' : '' }}>Stock Addition</option>
                        <option value="remove" {{ request('type') == 'remove' ? 'selected' : '' }}>Stock Removal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Reason</label>
                    <select name="reason" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                        <option value="">All Reasons</option>
                        <option value="damaged" {{ request('reason') == 'damaged' ? 'selected' : '' }}>Damaged Goods</option>
                        <option value="expired" {{ request('reason') == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="count_error" {{ request('reason') == 'count_error' ? 'selected' : '' }}>Count Error</option>
                        <option value="theft" {{ request('reason') == 'theft' ? 'selected' : '' }}>Theft</option>
                        <option value="donation" {{ request('reason') == 'donation' ? 'selected' : '' }}>Donation</option>
                        <option value="sample" {{ request('reason') == 'sample' ? 'selected' : '' }}>Sample</option>
                        <option value="other" {{ request('reason') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Filter
                    </button>
                    <a href="{{ route('stock-adjustments.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Adjustments Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adjustment #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Change</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($adjustments as $adjustment)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <a href="{{ route('stock-adjustments.show', $adjustment) }}" class="hover:text-blue-600">
                                    {{ $adjustment->adjustment_number }}
                                </a>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $adjustment->product->name }}</div>
                            <div class="text-sm text-gray-500">SKU: {{ $adjustment->product->sku }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($adjustment->type == 'add')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Addition
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Removal
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $adjustment->quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center">
                                <span class="text-gray-500">{{ $adjustment->previous_stock }}</span>
                                <i class="fas fa-arrow-right mx-2 text-gray-400"></i>
                                <span class="font-medium">{{ $adjustment->new_stock }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ ucfirst(str_replace('_', ' ', $adjustment->reason)) }}
                            @if($adjustment->description)
                            <div class="text-xs text-gray-500">{{ Str::limit($adjustment->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $adjustment->created_at->format('M d, Y') }}
                            <div class="text-xs text-gray-500">{{ $adjustment->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('stock-adjustments.show', $adjustment) }}" class="text-blue-600 hover:text-blue-900">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $adjustments->links() }}
        </div>
    </div>
</div>
@endsection