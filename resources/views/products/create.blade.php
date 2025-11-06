<!-- resources/views/products/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                        {{ isset($product) ? 'Edit Product' : 'Create New Product' }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Fill in the product details below. All fields marked with * are required.
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form action="{{ isset($product) ? route('products.update', $product) : route('products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($product))
                        @method('PUT')
                    @endif

                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <!-- Basic Information -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                                
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Product Name *</label>
                                        <input type="text" name="name" id="name" required
                                               value="{{ old('name', $product->name ?? '') }}"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Barcode Field -->
                                    <div>
                                        <label for="barcode" class="block text-sm font-medium text-gray-700">Barcode</label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <input type="text" name="barcode" id="barcode"
                                                   value="{{ old('barcode', $prefilledBarcode ?? ($product->barcode ?? '')) }}"
                                                   class="focus:ring-blue-500 focus:border-blue-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300"
                                                   placeholder="Leave empty to auto-generate">
                                            <button type="button" onclick="generateBarcode()" 
                                                    class="ml-3 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                Generate
                                            </button>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">Leave empty to auto-generate a barcode</p>
                                        @error('barcode')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="category_id" class="block text-sm font-medium text-gray-700">Category *</label>
                                            <select name="category_id" id="category_id" required
                                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                <option value="{{ $category->id }}" 
                                                    {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
                                            <select name="supplier_id" id="supplier_id"
                                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <option value="">Select Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}"
                                                    {{ old('supplier_id', $product->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                            @error('supplier_id')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Branch Field -->
                                    <div>
                                        <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch *</label>
                                        <select name="branch_id" id="branch_id" required
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">Select Branch</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" 
                                                    {{ old('branch_id', $product->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }} - {{ $branch->location }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea name="description" id="description" rows="3"
                                                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description', $product->description ?? '') }}</textarea>
                                        @error('description')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Pricing</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="purchase_price" class="block text-sm font-medium text-gray-700">Purchase Price *</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <input type="number" name="purchase_price" id="purchase_price" step="0.01" min="0" required
                                                   value="{{ old('purchase_price', $product->purchase_price ?? '0') }}"
                                                   class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                        @error('purchase_price')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="selling_price" class="block text-sm font-medium text-gray-700">Selling Price *</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <input type="number" name="selling_price" id="selling_price" step="0.01" min="0" required
                                                   value="{{ old('selling_price', $product->selling_price ?? '0') }}"
                                                   class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                        @error('selling_price')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Inventory -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Inventory</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                    <div>
                                        <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Current Stock *</label>
                                        <input type="number" name="stock_quantity" id="stock_quantity" min="0" required
                                               value="{{ old('stock_quantity', $product->stock_quantity ?? '0') }}"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('stock_quantity')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="min_stock" class="block text-sm font-medium text-gray-700">Min Stock *</label>
                                        <input type="number" name="min_stock" id="min_stock" min="0" required
                                               value="{{ old('min_stock', $product->min_stock ?? '5') }}"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('min_stock')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="max_stock" class="block text-sm font-medium text-gray-700">Max Stock *</label>
                                        <input type="number" name="max_stock" id="max_stock" min="0" required
                                               value="{{ old('max_stock', $product->max_stock ?? '100') }}"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('max_stock')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="unit" class="block text-sm font-medium text-gray-700">Unit *</label>
                                        <input type="text" name="unit" id="unit" required
                                               value="{{ old('unit', $product->unit ?? 'pcs') }}"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('unit')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Image & Settings -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Image & Settings</h4>
                                
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label for="image" class="block text-sm font-medium text-gray-700">Product Image</label>
                                        <input type="file" name="image" id="image"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('image')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                        
                                        @if(isset($product) && $product->image)
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500">Current Image:</p>
                                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="h-20 w-20 object-cover rounded mt-1">
                                        </div>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                                   {{ old('is_active', isset($product) ? $product->is_active : true) ? 'checked' : '' }}
                                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                                Product is active
                                            </label>
                                        </div>

                                        <div class="flex items-center">
                                            <!-- Hidden input to ensure value is always submitted -->
                                            <input type="hidden" name="track_stock" value="0">
                                            <input type="checkbox" name="track_stock" id="track_stock" value="1"
                                                   {{ old('track_stock', isset($product) ? $product->track_stock : true) ? 'checked' : '' }}
                                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                            <label for="track_stock" class="ml-2 block text-sm text-gray-900">
                                                Track stock quantity
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <a href="{{ route('products.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ isset($product) ? 'Update Product' : 'Create Product' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function generateBarcode() {
    // Generate a random 13-digit barcode
    let barcode = '20' + Math.floor(1000000000 + Math.random() * 9000000000);
    
    // Calculate EAN-13 check digit
    let sum = 0;
    for (let i = 0; i < 12; i++) {
        let digit = parseInt(barcode[i]);
        sum += (i % 2 === 0) ? digit : digit * 3;
    }
    let checkDigit = (10 - (sum % 10)) % 10;
    
    document.getElementById('barcode').value = barcode + checkDigit;
}

// Auto-focus barcode field if prefilled
@if(isset($prefilledBarcode) && $prefilledBarcode)
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('barcode').value = '{{ $prefilledBarcode }}';
    document.getElementById('barcode').focus();
});
@endif

// Price validation
document.addEventListener('DOMContentLoaded', function() {
    const purchasePrice = document.getElementById('purchase_price');
    const sellingPrice = document.getElementById('selling_price');
    
    function validatePrices() {
        if (parseFloat(purchasePrice.value) > parseFloat(sellingPrice.value)) {
            sellingPrice.setCustomValidity('Selling price must be greater than or equal to purchase price');
        } else {
            sellingPrice.setCustomValidity('');
        }
    }
    
    purchasePrice.addEventListener('input', validatePrices);
    sellingPrice.addEventListener('input', validatePrices);
});

// Stock level validation
document.addEventListener('DOMContentLoaded', function() {
    const minStock = document.getElementById('min_stock');
    const maxStock = document.getElementById('max_stock');
    
    function validateStockLevels() {
        if (parseInt(minStock.value) > parseInt(maxStock.value)) {
            maxStock.setCustomValidity('Max stock must be greater than or equal to min stock');
        } else {
            maxStock.setCustomValidity('');
        }
    }
    
    minStock.addEventListener('input', validateStockLevels);
    maxStock.addEventListener('input', validateStockLevels);
});
</script>
@endpush
@endsection