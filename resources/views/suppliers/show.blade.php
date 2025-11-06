<!-- resources/views/suppliers/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Supplier Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-16 w-16 bg-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-xl">
                                {{ strtoupper(substr($supplier->name, 0, 1)) }}
                            </span>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $supplier->name }}</h1>
                            <p class="text-gray-600">{{ $supplier->is_active ? 'Active' : 'Inactive' }} Supplier</p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        @can('edit_suppliers')
                        <a href="{{ route('suppliers.edit', $supplier) }}" 
                           class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Edit Supplier
                        </a>
                        @endcan
                        @can('create_purchase_orders')
                        <a href="{{ route('purchase-orders.create', ['supplier_id' => $supplier->id]) }}" 
                           class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            New Purchase Order
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Contact Information</h3>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-900">
                                Contact: {{ $supplier->contact_person ?? 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-900">
                                Email: {{ $supplier->email ?? 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-900">
                                Phone: {{ $supplier->phone ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Purchase Statistics</h3>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-900">
                                Total Orders: {{ $purchaseStats->total_orders ?? 0 }}
                            </p>
                            <p class="text-sm text-gray-900">
                                Total Spent: ${{ number_format($purchaseStats->total_spent ?? 0, 2) }}
                            </p>
                            <p class="text-sm text-gray-900">
                                Avg Order: ${{ number_format($purchaseStats->average_order ?? 0, 2) }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Activity</h3>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-900">
                                Products: {{ $supplier->products_count }}
                            </p>
                            <p class="text-sm text-gray-900">
                                First Order: {{ $purchaseStats->first_order ? $purchaseStats->first_order->format('M j, Y') : 'Never' }}
                            </p>
                            <p class="text-sm text-gray-900">
                                Last Order: {{ $purchaseStats->last_order ? $purchaseStats->last_order->format('M j, Y') : 'Never' }}
                            </p>
                        </div>
                    </div>
                </div>
                
                @if($supplier->address)
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-500">Address</h3>
                    <p class="mt-1 text-sm text-gray-900">{{ $supplier->address }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Products -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Products from this Supplier
                        </h3>
                        <a href="{{ route('suppliers.products', $supplier) }}" 
                           class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            View All
                        </a>
                    </div>
                </div>
                <div class="p-4">
                    @if($products->count() > 0)
                    <div class="space-y-3">
                        @foreach($products as $product)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $product->category->name }} • 
                                    @if($product->stock_quantity <= 0)
                                        <span class="text-red-600">Out of Stock</span>
                                    @elseif($product->stock_quantity <= $product->min_stock)
                                        <span class="text-yellow-600">Low Stock</span>
                                    @else
                                        <span class="text-green-600">In Stock: {{ $product->stock_quantity }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">${{ number_format($product->selling_price, 2) }}</p>
                                <p class="text-xs text-gray-500">Buy: ${{ number_format($product->purchase_price, 2) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-500 text-center py-4">No products from this supplier</p>
                    @endif
                </div>
            </div>

            <!-- Recent Purchase Orders -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Recent Purchase Orders
                        </h3>
                        <a href="{{ route('suppliers.purchase-orders', $supplier) }}" 
                           class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            View All
                        </a>
                    </div>
                </div>
                <div class="p-4">
                    @if($recentPurchaseOrders->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentPurchaseOrders as $order)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <div>
                                <p class="text-sm font-medium text-gray-900">PO #{{ $order->po_number }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $order->created_at->format('M j, Y') }} • 
                                    {{ $order->user->name }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">${{ number_format($order->total_amount, 2) }}</p>
                                <p class="text-xs text-gray-500 capitalize">{{ $order->status }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-500 text-center py-4">No purchase orders</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Supplier Actions -->
        <div class="mt-6 bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Supplier Management</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @can('edit_suppliers')
                <form action="{{ route('suppliers.toggle-status', $supplier) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" 
                            class="w-full bg-{{ $supplier->is_active ? 'red' : 'green' }}-500 text-white px-4 py-2 rounded hover:bg-{{ $supplier->is_active ? 'red' : 'green' }}-600">
                        {{ $supplier->is_active ? 'Deactivate Supplier' : 'Activate Supplier' }}
                    </button>
                </form>
                @endcan

                @can('delete_suppliers')
                @if($supplier->products_count === 0 && ($purchaseStats->total_orders ?? 0) === 0)
                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this supplier? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Delete Supplier
                    </button>
                </form>
                @endif
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection