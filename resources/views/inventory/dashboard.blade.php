@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Inventory Dashboard</h1>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-boxes text-3xl text-blue-500"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Products</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['total_products'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-3xl text-yellow-500"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Low Stock</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['low_stock_products'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-times-circle text-3xl text-red-500"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Out of Stock</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['out_of_stock_products'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-dollar-sign text-3xl text-green-500"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Stock Value</dt>
                                <dd class="text-lg font-medium text-gray-900">${{ number_format($stats['total_stock_value'], 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Low Stock Products -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Low Stock Products</h3>
                    <p class="mt-1 text-sm text-gray-600">Products that need restocking</p>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($lowStockProducts as $product)
                    <div class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($product->image)
                                        <img class="h-10 w-10 rounded-lg object-cover" src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}">
                                    @else
                                        <div class="h-10 w-10 bg-gray-300 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-gray-400"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                    <div class="text-sm text-gray-500">Current: {{ $product->stock_quantity }} | Min: {{ $product->min_stock }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Low Stock
                                </span>
                                <div class="text-sm text-gray-500 mt-1">
                                    <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-8 text-center">
                        <i class="fas fa-check-circle text-green-400 text-3xl mb-2"></i>
                        <p class="text-sm text-gray-600">No low stock products</p>
                    </div>
                    @endforelse
                </div>
                @if($lowStockProducts->count() > 0)
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <a href="{{ route('inventory.low-stock-report') }}" class="text-sm font-medium text-blue-600 hover:text-blue-900">
                        View all low stock products →
                    </a>
                </div>
                @endif
            </div>

            <!-- Recent Stock Movements -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Stock Movements</h3>
                    <p class="mt-1 text-sm text-gray-600">Latest inventory transactions</p>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($recentMovements as $movement)
                    <div class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $movement->product->name }}</div>
                                <div class="text-sm text-gray-500 flex items-center mt-1">
                                    @if($movement->quantity > 0)
                                        <span class="text-green-600 font-medium">+{{ $movement->quantity }}</span>
                                    @else
                                        <span class="text-red-600 font-medium">{{ $movement->quantity }}</span>
                                    @endif
                                    <span class="mx-2">•</span>
                                    <span>{{ $movement->reason }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-900">
                                    {{ $movement->created_at->format('M j, H:i') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $movement->user->name }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-8 text-center">
                        <i class="fas fa-exchange-alt text-gray-400 text-3xl mb-2"></i>
                        <p class="text-sm text-gray-600">No recent stock movements</p>
                    </div>
                    @endforelse
                </div>
                @if($recentMovements->count() > 0)
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <a href="{{ route('inventory.stock-movements') }}" class="text-sm font-medium text-blue-600 hover:text-blue-900">
                        View all stock movements →
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="{{ route('products.index') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <i class="fas fa-box text-blue-500 text-xl mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">Products</h3>
                        <p class="text-xs text-gray-500">Manage products</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('stock-adjustments.create') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <i class="fas fa-adjust text-purple-500 text-xl mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">Adjust Stock</h3>
                        <p class="text-xs text-gray-500">Make adjustments</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('inventory.stock-valuation') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <i class="fas fa-chart-line text-green-500 text-xl mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">Stock Value</h3>
                        <p class="text-xs text-gray-500">View valuation</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('purchase-orders.create') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <i class="fas fa-clipboard-list text-orange-500 text-xl mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">Purchase Order</h3>
                        <p class="text-xs text-gray-500">Create PO</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection