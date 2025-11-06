<!-- resources/views/users/show.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- User Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-16 w-16 bg-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-xl">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                            <p class="text-gray-600">{{ ucfirst($user->role) }} • {{ $user->is_active ? 'Active' : 'Inactive' }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        @can('edit_users')
                        <a href="{{ route('users.edit', $user) }}" 
                           class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Edit User
                        </a>
                        @endcan
                        @can('manage_permissions')
                        <a href="{{ route('users.permissions.edit', $user) }}" 
                           class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                            Manage Permissions
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
                            <p class="text-sm text-gray-900">{{ $user->email }}</p>
                            <p class="text-sm text-gray-900">{{ $user->phone ?? 'No phone' }}</p>
                            <p class="text-sm text-gray-900">PIN: {{ $user->pin_code }}</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Sales Performance</h3>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-900">Total Sales: {{ $salesStats->total_sales ?? 0 }}</p>
                            <p class="text-sm text-gray-900">Total Revenue: ${{ number_format($salesStats->total_revenue ?? 0, 2) }}</p>
                            <p class="text-sm text-gray-900">Average Sale: ${{ number_format($salesStats->average_sale ?? 0, 2) }}</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Activity</h3>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-900">First Sale: {{ $salesStats->first_sale ? $salesStats->first_sale->format('M j, Y') : 'Never' }}</p>
                            <p class="text-sm text-gray-900">Last Sale: {{ $salesStats->last_sale ? $salesStats->last_sale->format('M j, Y') : 'Never' }}</p>
                            <p class="text-sm text-gray-900">Member Since: {{ $user->created_at->format('M j, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Sales -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Recent Sales
                    </h3>
                </div>
                <div class="p-4">
                    @if($recentSales->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentSales as $sale)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Sale #{{ $sale->sale_number }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $sale->created_at->format('M j, Y H:i') }}
                                    @if($sale->customer)
                                    • {{ $sale->customer->name }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">${{ number_format($sale->total_amount, 2) }}</p>
                                <p class="text-xs text-gray-500 capitalize">{{ $sale->payment_method }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-500 text-center py-4">No sales recorded</p>
                    @endif
                </div>
            </div>

            <!-- User Actions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        User Management
                    </h3>
                </div>
                <div class="p-4 space-y-4">
                    @can('edit_users')
                    <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="w-full bg-{{ $user->is_active ? 'red' : 'green' }}-500 text-white px-4 py-2 rounded hover:bg-{{ $user->is_active ? 'red' : 'green' }}-600">
                            {{ $user->is_active ? 'Deactivate User' : 'Activate User' }}
                        </button>
                    </form>
                    @endcan

                    @can('edit_users')
                    <a href="{{ route('users.edit', $user) }}?tab=password" 
                       class="block w-full bg-yellow-500 text-white px-4 py-2 rounded text-center hover:bg-yellow-600">
                        Change Password
                    </a>
                    @endcan

                    @can('delete_users')
                    @if(auth()->id() !== $user->id && $user->sales_count === 0)
                    <form action="{{ route('users.destroy', $user) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                            Delete User
                        </button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection