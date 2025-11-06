@extends('layouts.app')

@section('title', $branch->name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Branch Details: {{ $branch->name }}</h3>
            <div class="flex space-x-2">
                <a href="{{ route('branches.inventory-report', $branch) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-chart-bar mr-2"></i> Inventory Report
                </a>
                @can('branches.manage')
                <a href="{{ route('branches.edit', $branch) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
                @endcan
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div>
                    <h5 class="text-lg font-medium text-gray-900 mb-4">Branch Information</h5>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-1 gap-3">
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="font-medium text-gray-700">Name:</span>
                                <span class="text-gray-900">{{ $branch->name }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="font-medium text-gray-700">Location:</span>
                                <span class="text-gray-900">{{ $branch->location }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="font-medium text-gray-700">Phone:</span>
                                <span class="text-gray-900">{{ $branch->phone ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="font-medium text-gray-700">Email:</span>
                                <span class="text-gray-900">{{ $branch->email ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="font-medium text-gray-700">Manager:</span>
                                <span class="text-gray-900">{{ $branch->manager_name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="font-medium text-gray-700">Status:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h5 class="text-lg font-medium text-gray-900 mb-4">Inventory Summary</h5>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="flex items-center">
                                <div class="bg-blue-100 p-3 rounded-lg mr-4">
                                    <i class="fas fa-boxes text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-blue-900">Total Products</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ $branch->products->count() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                            <div class="flex items-center">
                                <div class="bg-yellow-100 p-3 rounded-lg mr-4">
                                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-yellow-900">Low Stock</p>
                                    <p class="text-2xl font-bold text-yellow-600">{{ $lowStockProducts->count() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <div class="flex items-center">
                                <div class="bg-red-100 p-3 rounded-lg mr-4">
                                    <i class="fas fa-times text-red-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-red-900">Out of Stock</p>
                                    <p class="text-2xl font-bold text-red-600">{{ $outOfStockProducts->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($lowStockProducts->count() > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                    <h6 class="font-medium text-yellow-800">Low Stock Products</h6>
                </div>
                <ul class="text-sm text-yellow-700 list-disc list-inside">
                    @foreach($lowStockProducts as $product)
                        <li>{{ $product->name }} - Current: {{ $product->quantity }}, Reorder Level: {{ $product->reorder_level }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($outOfStockProducts->count() > 0)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-center mb-2">
                    <i class="fas fa-times text-red-600 mr-2"></i>
                    <h6 class="font-medium text-red-800">Out of Stock Products</h6>
                </div>
                <ul class="text-sm text-red-700 list-disc list-inside">
                    @foreach($outOfStockProducts as $product)
                        <li>{{ $product->name }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <h5 class="text-lg font-medium text-gray-900 mb-4">Products in this Branch</h5>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Services</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($branch->products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->reorder_level }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($product->price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($product->quantity == 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Out of Stock</span>
                                    @elseif($product->quantity <= $product->reorder_level)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Low Stock</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">In Stock</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @foreach($product->services as $service)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-1 mb-1">{{ $service->name }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No products found in this branch.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection