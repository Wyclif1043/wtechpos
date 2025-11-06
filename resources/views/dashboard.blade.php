@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Enhanced Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Products Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-300">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-ocean-50 to-cyan-50 rounded-lg flex items-center justify-center">
                            <i class="fas fa-box text-ocean-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-600">Total Products</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $stats['total_products'] }}</dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-300">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-amber-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-600">Low Stock</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $stats['low_stock_products'] }}</dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Customers Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-300">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-50 to-cyan-50 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-emerald-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-600">Total Customers</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $stats['total_customers'] }}</dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Sales Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-300">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-cyan-50 to-ocean-50 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-cyan-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-600">Today's Sales</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $stats['today_sales'] }}</dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('pos.interface') }}" 
               class="group bg-gradient-to-br from-ocean-50 to-cyan-50 hover:from-ocean-100 hover:to-cyan-100 border border-ocean-100 rounded-lg p-4 text-center transition-all duration-300">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-cash-register text-ocean-600"></i>
                </div>
                <div class="font-medium text-gray-900">POS Interface</div>
            </a>
            
            <a href="{{ route('products.index') }}" 
               class="group bg-gradient-to-br from-emerald-50 to-cyan-50 hover:from-emerald-100 hover:to-cyan-100 border border-emerald-100 rounded-lg p-4 text-center transition-all duration-300">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-boxes text-emerald-600"></i>
                </div>
                <div class="font-medium text-gray-900">Products</div>
            </a>
            
            <a href="{{ route('customers.index') }}" 
               class="group bg-gradient-to-br from-purple-50 to-pink-50 hover:from-purple-100 hover:to-pink-100 border border-purple-100 rounded-lg p-4 text-center transition-all duration-300">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-users text-purple-600"></i>
                </div>
                <div class="font-medium text-gray-900">Customers</div>
            </a>
            
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.users') }}" 
               class="group bg-gradient-to-br from-amber-50 to-orange-50 hover:from-amber-100 hover:to-orange-100 border border-amber-100 rounded-lg p-4 text-center transition-all duration-300">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-cog text-amber-600"></i>
                </div>
                <div class="font-medium text-gray-900">Admin</div>
            </a>
            @endif
        </div>
    </div>
</div>
@endsection