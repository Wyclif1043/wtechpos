@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Stock Adjustment: {{ $stockAdjustment->adjustment_number }}</h1>
                <p class="text-sm text-gray-600">Created on {{ $stockAdjustment->created_at->format('M d, Y') }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('stock-adjustments.index') }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Back to List
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Adjustment Details -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Adjustment Details</h3>
                    </div>
                    <div class="border-t border-gray-200">
                        <dl>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Product</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <div class="font-medium">{{ $stockAdjustment->product->name }}</div>
                                    <div class="text-gray-600">SKU: {{ $stockAdjustment->product->sku }}</div>
                                    <div class="text-gray-600">Category: {{ $stockAdjustment->product->category->name }}</div>
                                </dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Adjustment Type</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    @if($stockAdjustment->type == 'add')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Stock Addition
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Stock Removal
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Quantity</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-bold">
                                    {{ $stockAdjustment->quantity }}
                                </dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Stock Change</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <div class="flex items-center space-x-4">
                                        <span class="text-gray-600">{{ $stockAdjustment->previous_stock }}</span>
                                        <i class="fas fa-arrow-right text-gray-400"></i>
                                        <span class="font-bold text-lg 
                                            {{ $stockAdjustment->type == 'add' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $stockAdjustment->new_stock }}
                                        </span>
                                        <span class="text-sm {{ $stockAdjustment->type == 'add' ? 'text-green-600' : 'text-red-600' }}">
                                            ({{ $stockAdjustment->type == 'add' ? '+' : '-' }}{{ $stockAdjustment->quantity }})
                                        </span>
                                    </div>
                                </dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Reason</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ ucfirst(str_replace('_', ' ', $stockAdjustment->reason)) }}
                                </dd>
                            </div>
                            @if($stockAdjustment->cost_value)
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Cost Value</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    ${{ number_format($stockAdjustment->cost_value, 2) }}
                                </dd>
                            </div>
                            @endif
                            @if($stockAdjustment->description)
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $stockAdjustment->description }}
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Adjustment Info -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Adjustment Information</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Adjustment Number</div>
                            <div class="text-sm text-gray-900">{{ $stockAdjustment->adjustment_number }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Date & Time</div>
                            <div class="text-sm text-gray-900">{{ $stockAdjustment->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Performed By</div>
                            <div class="text-sm text-gray-900">{{ $stockAdjustment->user->name }}</div>
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Product Information</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 space-y-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Current Stock</div>
                            <div class="text-sm text-gray-900 font-bold">{{ $stockAdjustment->product->stock_quantity }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Purchase Price</div>
                            <div class="text-sm text-gray-900">${{ number_format($stockAdjustment->product->purchase_price, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Selling Price</div>
                            <div class="text-sm text-gray-900">${{ number_format($stockAdjustment->product->selling_price, 2) }}</div>
                        </div>
                        <div>
                            <a href="{{ route('products.show', $stockAdjustment->product) }}" 
                               class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                View Product Details →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection