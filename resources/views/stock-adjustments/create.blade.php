@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Create Stock Adjustment</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Adjust stock levels for inventory accuracy.
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form action="{{ route('stock-adjustments.store') }}" method="POST">
                    @csrf

                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <!-- Product Selection -->
                            <div>
                                <label for="product_id" class="block text-sm font-medium text-gray-700">Product *</label>
                                <select name="product_id" id="product_id" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                        {{ old('product_id') == $product->id ? 'selected' : '' }}
                                        data-current-stock="{{ $product->stock_quantity }}">
                                        {{ $product->name }} (SKU: {{ $product->sku }}) - Current Stock: {{ $product->stock_quantity }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Adjustment Type and Quantity -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">Adjustment Type *</label>
                                    <select name="type" id="type" required
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Select Type</option>
                                        <option value="add" {{ old('type') == 'add' ? 'selected' : '' }}>Add Stock</option>
                                        <option value="remove" {{ old('type') == 'remove' ? 'selected' : '' }}>Remove Stock</option>
                                    </select>
                                    @error('type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity *</label>
                                    <input type="number" name="quantity" id="quantity" min="1" required
                                           value="{{ old('quantity', 1) }}"
                                           class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('quantity')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <div id="stock-warning" class="mt-2 text-sm text-red-600 hidden"></div>
                                </div>
                            </div>

                            <!-- Reason and Description -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="reason" class="block text-sm font-medium text-gray-700">Reason *</label>
                                    <select name="reason" id="reason" required
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Select Reason</option>
                                        <option value="damaged" {{ old('reason') == 'damaged' ? 'selected' : '' }}>Damaged Goods</option>
                                        <option value="expired" {{ old('reason') == 'expired' ? 'selected' : '' }}>Expired</option>
                                        <option value="count_error" {{ old('reason') == 'count_error' ? 'selected' : '' }}>Count Error</option>
                                        <option value="theft" {{ old('reason') == 'theft' ? 'selected' : '' }}>Theft</option>
                                        <option value="donation" {{ old('reason') == 'donation' ? 'selected' : '' }}>Donation</option>
                                        <option value="sample" {{ old('reason') == 'sample' ? 'selected' : '' }}>Sample</option>
                                        <option value="other" {{ old('reason') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('reason')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="cost_value" class="block text-sm font-medium text-gray-700">Cost Value (Optional)</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" name="cost_value" id="cost_value" step="0.01" min="0"
                                               value="{{ old('cost_value') }}"
                                               class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    @error('cost_value')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="3"
                                          placeholder="Provide details about this adjustment..."
                                          class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Preview Section -->
                            <div id="preview-section" class="bg-gray-50 p-4 rounded-lg hidden">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Adjustment Preview</h4>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">Current Stock:</span>
                                        <span id="preview-current" class="font-medium ml-2">-</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Adjustment:</span>
                                        <span id="preview-adjustment" class="font-medium ml-2">-</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">New Stock:</span>
                                        <span id="preview-new" class="font-medium ml-2">-</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Change:</span>
                                        <span id="preview-change" class="font-medium ml-2">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <a href="{{ route('stock-adjustments.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Create Adjustment
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
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const typeSelect = document.getElementById('type');
    const quantityInput = document.getElementById('quantity');
    const previewSection = document.getElementById('preview-section');
    const stockWarning = document.getElementById('stock-warning');

    function updatePreview() {
        const selectedProduct = productSelect.options[productSelect.selectedIndex];
        const currentStock = selectedProduct ? parseInt(selectedProduct.dataset.currentStock) : 0;
        const type = typeSelect.value;
        const quantity = parseInt(quantityInput.value) || 0;

        if (productSelect.value && type && quantity > 0) {
            const adjustment = type === 'add' ? quantity : -quantity;
            const newStock = currentStock + adjustment;

            document.getElementById('preview-current').textContent = currentStock;
            document.getElementById('preview-adjustment').textContent = 
                type === 'add' ? `+${quantity}` : `-${quantity}`;
            document.getElementById('preview-new').textContent = newStock;
            document.getElementById('preview-change').textContent = 
                type === 'add' ? `Increase by ${quantity}` : `Decrease by ${quantity}`;

            previewSection.classList.remove('hidden');

            // Show warning for insufficient stock
            if (type === 'remove' && quantity > currentStock) {
                stockWarning.textContent = `Warning: Insufficient stock. Current stock: ${currentStock}`;
                stockWarning.classList.remove('hidden');
            } else {
                stockWarning.classList.add('hidden');
            }
        } else {
            previewSection.classList.add('hidden');
            stockWarning.classList.add('hidden');
        }
    }

    productSelect.addEventListener('change', updatePreview);
    typeSelect.addEventListener('change', updatePreview);
    quantityInput.addEventListener('input', updatePreview);

    // Initial preview update
    updatePreview();
});
</script>
@endpush
@endsection