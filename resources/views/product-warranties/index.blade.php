@extends('layouts.app')

@section('page-title', 'Product Warranties')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Product Warranties</h1>
                <p class="text-gray-600 mt-2">Manage warranty templates for your products</p>
            </div>
            <a href="{{ route('product-warranties.create') }}" 
               class="bg-gradient-to-br from-ocean-600 to-cyan-600 text-white px-6 py-3 rounded-lg hover:from-ocean-700 hover:to-cyan-700 transition-all duration-200 font-medium">
                <i class="fas fa-plus mr-2"></i>
                Add Warranty Template
            </a>
        </div>

        <!-- Product Warranties Table -->
        <div class="bg-white rounded-2xl shadow-soft border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Warranty Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Coverage</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($warranties as $warranty)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <!-- Product Column -->
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    @if($warranty->product->image)
                                    <div class="flex-shrink-0">
                                        <img class="h-10 w-10 rounded-lg object-cover border border-gray-200" 
                                             src="{{ Storage::url($warranty->product->image) }}" 
                                             alt="{{ $warranty->product->name }}">
                                    </div>
                                    @else
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200">
                                            <i class="fas fa-box text-gray-400"></i>
                                        </div>
                                    </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $warranty->product->name }}</div>
                                        <div class="text-xs text-gray-500">SKU: {{ $warranty->product->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Warranty Name -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $warranty->warranty_name }}</div>
                            </td>
                            
                            <!-- Type -->
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst($warranty->type) }}
                                </span>
                            </td>
                            
                            <!-- Duration -->
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $warranty->formatted_duration }}</div>
                            </td>
                            
                            <!-- Coverage -->
                            <td class="px-6 py-4">
                                @if($warranty->coverage_details)
                                    <div class="text-sm text-gray-600 max-w-xs truncate" title="{{ $warranty->coverage_details }}">
                                        {{ $warranty->coverage_details }}
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">No coverage details</span>
                                @endif
                            </td>
                            
                            <!-- Status -->
                            <td class="px-6 py-4">
                                @if($warranty->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        <i class="fas fa-check-circle mr-1 text-xs"></i>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-pause-circle mr-1 text-xs"></i>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            
                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <!-- View -->
                                    <a href="{{ route('product-warranties.show', $warranty) }}" 
                                       class="text-ocean-600 hover:text-ocean-700 transition-colors duration-200 p-2 rounded-lg hover:bg-ocean-50"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <!-- Edit -->
                                    <a href="{{ route('product-warranties.edit', $warranty) }}" 
                                       class="text-blue-600 hover:text-blue-700 transition-colors duration-200 p-2 rounded-lg hover:bg-blue-50"
                                       title="Edit Warranty">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <!-- Delete -->
                                    <form action="{{ route('product-warranties.destroy', $warranty) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-700 transition-colors duration-200 p-2 rounded-lg hover:bg-red-50"
                                                onclick="return confirm('Are you sure you want to delete this warranty template?')"
                                                title="Delete Warranty">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-shield-alt text-gray-400 text-xl"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">No Product Warranties</h4>
                                <p class="text-gray-600 mb-4">Create warranty templates for your products to get started.</p>
                                <a href="{{ route('product-warranties.create') }}" 
                                   class="inline-flex items-center text-ocean-600 hover:text-ocean-700 font-medium">
                                    <i class="fas fa-plus mr-2"></i>
                                    Create First Warranty
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($warranties->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $warranties->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.shadow-soft {
    box-shadow: 0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04);
}

/* Custom truncate for long text */
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
@endsection