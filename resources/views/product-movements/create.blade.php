@extends('layouts.app')

@section('title', 'Create Transfer Request')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Create Transfer Request</h3>
            <p class="text-sm text-gray-600 mt-1">Request product transfer from your branch to another branch</p>
        </div>
        
        <div class="p-6">
            <form action="{{ route('product-movements.store') }}" method="POST" enctype="multipart/form-data" id="transferForm">
                @csrf
                
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-medium text-blue-900 mb-2">Source Information</h4>
                    <p class="text-sm text-blue-700">
                        <strong>From Branch:</strong> {{ auth()->user()->branch->name ?? 'Your Branch' }}<br>
                        <strong>Requested By:</strong> {{ auth()->user()->name }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">Product *</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('product_id') border-red-500 @enderror" 
                                id="product_id" name="product_id" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                    {{ old('product_id') == $product->id ? 'selected' : '' }}
                                    data-stock="{{ $product->stock_quantity }}">
                                    {{ $product->name }} (SKU: {{ $product->sku }}) - Stock: {{ $product->stock_quantity }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="to_branch_id" class="block text-sm font-medium text-gray-700 mb-2">Destination Branch *</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('to_branch_id') border-red-500 @enderror" 
                                id="to_branch_id" name="to_branch_id" required>
                            <option value="">Select Destination Branch</option>
                            @foreach($branches as $branch)
                                @if($branch->id != auth()->user()->branch_id)
                                    <option value="{{ $branch->id }}" 
                                        {{ old('to_branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }} - {{ $branch->location }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('to_branch_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                    <input type="number" class="w-full md:w-1/2 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('quantity') border-red-500 @enderror" 
                           id="quantity" name="quantity" value="{{ old('quantity') }}" 
                           min="1" required>
                    <div id="stock-info" class="mt-2 text-sm text-gray-600 hidden">
                        Available stock: <span id="available-stock" class="font-medium"></span>
                    </div>
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Transfer *</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('reason') border-red-500 @enderror" 
                              id="reason" name="reason" rows="3" placeholder="Explain why this transfer is needed..." required>{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror" 
                              id="notes" name="notes" rows="2" placeholder="Any additional information...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="document" class="block text-sm font-medium text-gray-700 mb-2">Supporting Document (Optional)</label>
                    <input type="file" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('document') border-red-500 @enderror" 
                           id="document" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <p class="mt-1 text-sm text-gray-500">PDF, JPG, PNG, DOC (Max: 2MB)</p>
                    @error('document')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex space-x-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition-colors flex items-center">
                        <i class="fas fa-paper-plane mr-2"></i> Submit Transfer Request
                    </button>
                    <a href="{{ route('product-movements.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-md transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const quantityInput = document.getElementById('quantity');
    const stockInfo = document.getElementById('stock-info');
    const availableStock = document.getElementById('available-stock');

    function updateStockInfo() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const stock = selectedOption.getAttribute('data-stock');
            availableStock.textContent = stock;
            stockInfo.classList.remove('hidden');
            
            // Set max quantity
            quantityInput.max = stock;
            
            // Validate current quantity
            if (parseInt(quantityInput.value) > parseInt(stock)) {
                quantityInput.setCustomValidity('Quantity cannot exceed available stock');
            } else {
                quantityInput.setCustomValidity('');
            }
        } else {
            stockInfo.classList.add('hidden');
            quantityInput.setCustomValidity('');
        }
    }

    productSelect.addEventListener('change', updateStockInfo);
    quantityInput.addEventListener('input', updateStockInfo);

    // Initialize on page load
    updateStockInfo();

    // Form validation
    document.getElementById('transferForm').addEventListener('submit', function(e) {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const stock = selectedOption ? parseInt(selectedOption.getAttribute('data-stock')) : 0;
        const quantity = parseInt(quantityInput.value);

        if (quantity > stock) {
            e.preventDefault();
            alert('Error: Quantity cannot exceed available stock of ' + stock);
        }
    });
});
</script>
@endpush
@endsection