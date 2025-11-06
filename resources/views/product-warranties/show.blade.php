@extends('layouts.app')

@section('page-title', 'Product Warranty Details')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Warranty Details</h1>
                <p class="text-gray-600 mt-2">{{ $productWarranty->warranty_name }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('product-warranties.edit', $productWarranty) }}" 
                   class="bg-ocean-600 text-white px-6 py-3 rounded-lg hover:bg-ocean-700 transition-all duration-200 font-medium">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Warranty
                </a>
                <a href="{{ route('product-warranties.index') }}" 
                   class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to List
                </a>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Main Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Product Information Card -->
                <div class="bg-white rounded-2xl shadow-soft border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">Product Information</h3>
                        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-box text-blue-600 text-lg"></i>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Product Name</label>
                                <p class="text-gray-900 font-semibold">{{ $productWarranty->product->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">SKU</label>
                                <p class="text-gray-900 font-mono bg-gray-50 px-3 py-1 rounded-lg inline-block">{{ $productWarranty->product->sku }}</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Barcode</label>
                                <p class="text-gray-900">{{ $productWarranty->product->barcode ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Category</label>
                                <p class="text-gray-900">{{ $productWarranty->product->category->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warranty Information Card -->
                <div class="bg-white rounded-2xl shadow-soft border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">Warranty Information</h3>
                        <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-shield-alt text-green-600 text-lg"></i>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Warranty Name</label>
                                <p class="text-gray-900 font-semibold">{{ $productWarranty->warranty_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Type</label>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst($productWarranty->type) }}
                                </span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Duration</label>
                                <p class="text-gray-900 font-semibold text-lg">{{ $productWarranty->formatted_duration }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                                @if($productWarranty->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-pause-circle mr-1"></i>
                                        Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-600 mb-1">Created Date</label>
                        <p class="text-gray-900">{{ $productWarranty->created_at->format('F d, Y') }}</p>
                    </div>
                </div>

                <!-- Coverage Details Card -->
                @if($productWarranty->coverage_details)
                <div class="bg-white rounded-2xl shadow-soft border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">Coverage Details</h3>
                        <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-list-check text-purple-600 text-lg"></i>
                        </div>
                    </div>
                    <div class="prose prose-sm max-w-none">
                        <div class="text-gray-700 leading-relaxed bg-gray-50 rounded-lg p-4">
                            {!! nl2br(e($productWarranty->coverage_details)) !!}
                        </div>
                    </div>
                </div>
                @endif

                <!-- Terms & Conditions Card -->
                @if($productWarranty->terms)
                <div class="bg-white rounded-2xl shadow-soft border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">Terms & Conditions</h3>
                        <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-file-contract text-amber-600 text-lg"></i>
                        </div>
                    </div>
                    <div class="prose prose-sm max-w-none">
                        <div class="text-gray-700 leading-relaxed bg-gray-50 rounded-lg p-4">
                            {!! nl2br(e($productWarranty->terms)) !!}
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column - Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions Card -->
                <div class="bg-white rounded-2xl shadow-soft border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                        <div class="w-10 h-10 bg-ocean-50 rounded-lg flex items-center justify-center">
                            <i class="fas fa-bolt text-ocean-600"></i>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <a href="{{ route('products.show', $productWarranty->product_id) }}" 
                           class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 font-medium">
                            <i class="fas fa-box mr-3"></i>
                            View Product Details
                        </a>
                        
                        @if($productWarranty->is_active)
                        <form action="{{ route('product-warranties.update', $productWarranty) }}" method="POST" class="w-full">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="warranty_name" value="{{ $productWarranty->warranty_name }}">
                            <input type="hidden" name="type" value="{{ $productWarranty->type }}">
                            <input type="hidden" name="duration_months" value="{{ $productWarranty->duration_months }}">
                            <input type="hidden" name="terms" value="{{ $productWarranty->terms }}">
                            <input type="hidden" name="coverage_details" value="{{ $productWarranty->coverage_details }}">
                            <input type="hidden" name="is_active" value="0">
                            <button type="submit" 
                                    class="w-full flex items-center justify-center px-4 py-3 border border-amber-300 rounded-lg text-amber-700 hover:bg-amber-50 hover:border-amber-400 transition-all duration-200 font-medium">
                                <i class="fas fa-times mr-3"></i>
                                Deactivate Warranty
                            </button>
                        </form>
                        @else
                        <form action="{{ route('product-warranties.update', $productWarranty) }}" method="POST" class="w-full">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="warranty_name" value="{{ $productWarranty->warranty_name }}">
                            <input type="hidden" name="type" value="{{ $productWarranty->type }}">
                            <input type="hidden" name="duration_months" value="{{ $productWarranty->duration_months }}">
                            <input type="hidden" name="terms" value="{{ $productWarranty->terms }}">
                            <input type="hidden" name="coverage_details" value="{{ $productWarranty->coverage_details }}">
                            <input type="hidden" name="is_active" value="1">
                            <button type="submit" 
                                    class="w-full flex items-center justify-center px-4 py-3 border border-emerald-300 rounded-lg text-emerald-700 hover:bg-emerald-50 hover:border-emerald-400 transition-all duration-200 font-medium">
                                <i class="fas fa-check mr-3"></i>
                                Activate Warranty
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <!-- Product Summary Card -->
                <div class="bg-white rounded-2xl shadow-soft border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Product Summary</h3>
                        <div class="w-10 h-10 bg-gray-50 rounded-lg flex items-center justify-center">
                            <i class="fas fa-info-circle text-gray-600"></i>
                        </div>
                    </div>
                    <div class="text-center">
                        @if($productWarranty->product->image)
                        <img src="{{ $productWarranty->product->image_url }}" 
                             alt="{{ $productWarranty->product->name }}" 
                             class="w-32 h-32 object-cover rounded-xl mx-auto mb-4 border-2 border-gray-200 shadow-sm">
                        @else
                        <div class="w-32 h-32 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4 border-2 border-gray-200">
                            <i class="fas fa-box text-gray-400 text-3xl"></i>
                        </div>
                        @endif
                        
                        <h4 class="font-bold text-gray-900 mb-3 text-lg">{{ $productWarranty->product->name }}</h4>
                        
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-lg">
                                <span class="text-gray-600 font-medium">SKU:</span>
                                <span class="font-mono text-gray-900">{{ $productWarranty->product->sku }}</span>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-lg">
                                <span class="text-gray-600 font-medium">Price:</span>
                                <span class="font-bold text-green-600">${{ number_format($productWarranty->product->selling_price, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-lg">
                                <span class="text-gray-600 font-medium">Stock:</span>
                                <span class="font-medium">
                                    @if($productWarranty->product->track_stock)
                                        <span class="text-blue-600">{{ $productWarranty->product->stock_quantity }} units</span>
                                    @else
                                        <span class="text-gray-400">Not tracked</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.shadow-soft {
    box-shadow: 0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04);
}

.prose {
    color: inherit;
}
</style>
@endsection