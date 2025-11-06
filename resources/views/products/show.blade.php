<!-- resources/views/products/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Product Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        @if($product->image)
                        <div class="flex-shrink-0 h-16 w-16">
                            <img class="h-16 w-16 rounded-full object-cover" src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}">
                        </div>
                        @else
                        <div class="flex-shrink-0 h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="text-gray-500 text-lg font-medium">
                                {{ strtoupper(substr($product->name, 0, 1)) }}
                            </span>
                        </div>
                        @endif
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
                            <p class="text-gray-600">
                                {{ $product->category->name ?? 'No Category' }}
                                @if($product->supplier)
                                • Supplier: {{ $product->supplier->name }}
                                @endif
                                @if($product->branch)
                                • Branch: {{ $product->branch->name }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2">
                        @can('edit_products')
                        <form action="{{ route('products.regenerate-barcode', $product) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="w-full bg-blue-500 text-white px-4 py-2 rounded text-sm hover:bg-blue-600"
                                    onclick="return confirm('Are you sure you want to regenerate the barcode?')">
                                Regenerate Barcode
                            </button>
                        </form>
                        
                        <form action="{{ route('products.regenerate-sku', $product) }}" method="POST" class="inline w-full">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="w-full bg-green-500 text-white px-4 py-2 rounded text-sm hover:bg-green-600 mt-2"
                                    onclick="return confirm('Are you sure you want to regenerate the SKU?')">
                                Regenerate SKU
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>

            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Product Info</h3>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-900">
                                SKU: {{ $product->sku ?? 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-900">
                                Barcode: {{ $product->barcode ?? 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-900">
                                Unit: {{ $product->unit }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Pricing</h3>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-900">
                                Purchase: ${{ number_format($product->purchase_price, 2) }}
                            </p>
                            <p class="text-sm text-gray-900">
                                Selling: ${{ number_format($product->selling_price, 2) }}
                            </p>
                            <p class="text-sm text-gray-900">
                                Margin: ${{ number_format($product->selling_price - $product->purchase_price, 2) }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Inventory</h3>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-900">
                                Stock: {{ $product->stock_quantity }} {{ $product->unit }}
                            </p>
                            <p class="text-sm text-gray-900">
                                Min: {{ $product->min_stock }} {{ $product->unit }}
                            </p>
                            <p class="text-sm text-gray-900">
                                Max: {{ $product->max_stock }} {{ $product->unit }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Status</h3>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm">
                                @if(!$product->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @elseif($product->stock_quantity <= 0)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Out of Stock</span>
                                @elseif($product->stock_quantity <= $product->min_stock)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Low Stock</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">In Stock</span>
                                @endif
                            </p>
                            <p class="text-sm text-gray-900">
                                Stock Value: ${{ number_format($product->stock_value, 2) }}
                            </p>
                            <p class="text-sm text-gray-900">
                                Track Stock: {{ $product->track_stock ? 'Yes' : 'No' }}
                            </p>
                        </div>
                    </div>
                </div>
                
                @if($product->description)
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-500">Description</h3>
                    <p class="mt-1 text-sm text-gray-900">{{ $product->description }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Sales Statistics -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Sales Statistics
                    </h3>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Total Sold:</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $salesStats->total_sold ?? 0 }} {{ $product->unit }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Total Revenue:</span>
                            <span class="text-sm font-semibold text-gray-900">${{ number_format($salesStats->total_revenue ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Average Price:</span>
                            <span class="text-sm font-semibold text-gray-900">${{ number_format($salesStats->average_price ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Monthly Sales:</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $product->monthly_sales }} {{ $product->unit }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Stock Movements -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Recent Stock Movements
                    </h3>
                </div>
                <div class="p-4">
                    @if($product->stockMovements->count() > 0)
                    <div class="space-y-3">
                        @foreach($product->stockMovements as $movement)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <div>
                                <p class="text-sm font-medium text-gray-900 capitalize">{{ str_replace('_', ' ', $movement->type) }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $movement->created_at->format('M j, H:i') }} • {{ $movement->user->name }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold {{ $movement->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                </p>
                                <p class="text-xs text-gray-500">Stock: {{ $movement->new_stock }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-500 text-center py-4">No stock movements recorded</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection