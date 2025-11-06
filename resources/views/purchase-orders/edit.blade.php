@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Edit Purchase Order</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Update the purchase order details below.
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form action="{{ route('purchase-orders.update', $purchaseOrder) }}" method="POST" id="purchaseOrderForm">
                    @csrf
                    @method('PUT')

                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <!-- Supplier Information -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Supplier Information</h4>
                                
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier *</label>
                                        <select name="supplier_id" id="supplier_id" required
                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">Select Supplier</option>
                                            @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" 
                                                {{ $purchaseOrder->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }} - {{ $supplier->contact_person ?? 'No contact' }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('supplier_id')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="order_date" class="block text-sm font-medium text-gray-700">Order Date *</label>
                                            <input type="date" name="order_date" id="order_date" required
                                                   value="{{ $purchaseOrder->order_date->format('Y-m-d') }}"
                                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            @error('order_date')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700">Expected Delivery</label>
                                            <input type="date" name="expected_delivery_date" id="expected_delivery_date"
                                                   value="{{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('Y-m-d') : '' }}"
                                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            @error('expected_delivery_date')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Order Items</h4>
                                
                                <div id="items-container">
                                    @foreach($purchaseOrder->items as $index => $item)
                                    <div class="item-row border rounded-lg p-4 mb-4" id="item-{{ $index }}">
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Product</label>
                                                <div class="mt-1 text-sm font-medium">{{ $item->product->name }}</div>
                                                <div class="text-sm text-gray-500">SKU: {{ $item->product->sku }}</div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Quantity *</label>
                                                <input type="number" name="items[{{ $index }}][quantity_ordered]" 
                                                       value="{{ $item->quantity_ordered }}" min="1" required
                                                       class="mt-1 quantity-input focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Unit Cost *</label>
                                                <input type="number" name="items[{{ $index }}][unit_cost]" 
                                                       value="{{ $item->unit_cost }}" step="0.01" min="0" required
                                                       class="mt-1 cost-input focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                            <div class="flex items-end">
                                                <button type="button" class="remove-item bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="mt-4">
                                    <button type="button" id="add-item" 
                                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 flex items-center">
                                        <i class="fas fa-plus mr-2"></i>
                                        Add Item
                                    </button>
                                </div>

                                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-medium">Total Amount:</span>
                                        <span id="total-amount" class="text-2xl font-bold text-blue-600">
                                            ${{ number_format($purchaseOrder->total_amount, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="notes" id="notes" rows="3"
                                          class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ $purchaseOrder->notes }}</textarea>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Update Purchase Order
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Product Selection Modal -->
<div id="product-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Select Product</h3>
                <button type="button" id="close-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mb-4">
                <input type="text" id="product-search" placeholder="Search products..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div id="product-list" class="max-h-96 overflow-y-auto">
                @foreach($products as $categoryName => $categoryProducts)
                <div class="mb-4">
                    <h4 class="font-medium text-gray-900 mb-2">{{ $categoryName }}</h4>
                    <div class="space-y-2">
                        @foreach($categoryProducts as $product)
                        <div class="product-item flex justify-between items-center p-2 hover:bg-gray-50 cursor-pointer border rounded"
                             data-product-id="{{ $product->id }}"
                             data-product-name="{{ $product->name }}"
                             data-product-sku="{{ $product->sku }}"
                             data-product-price="{{ $product->purchase_price }}"
                             data-product-stock="{{ $product->stock_quantity }}">
                            <div>
                                <div class="font-medium">{{ $product->name }}</div>
                                <div class="text-sm text-gray-500">SKU: {{ $product->sku }} | Stock: {{ $product->stock_quantity }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">${{ number_format($product->purchase_price, 2) }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let itemCount = {{ $purchaseOrder->items->count() }};
let currentItemIndex = null;

document.getElementById('add-item').addEventListener('click', function() {
    currentItemIndex = itemCount;
    document.getElementById('product-modal').classList.remove('hidden');
});

document.getElementById('close-modal').addEventListener('click', function() {
    document.getElementById('product-modal').classList.add('hidden');
});

// Product search
document.getElementById('product-search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const productItems = document.querySelectorAll('.product-item');
    
    productItems.forEach(item => {
        const productName = item.querySelector('.font-medium').textContent.toLowerCase();
        const productSku = item.dataset.productSku.toLowerCase();
        
        if (productName.includes(searchTerm) || productSku.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
});

// Product selection
document.querySelectorAll('.product-item').forEach(item => {
    item.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const productName = this.dataset.productName;
        const productPrice = this.dataset.productPrice;
        
        addItemToForm(productId, productName, productPrice);
        document.getElementById('product-modal').classList.add('hidden');
    });
});

function addItemToForm(productId, productName, productPrice) {
    const itemId = `item-${itemCount}`;
    
    const itemHtml = `
        <div class="item-row border rounded-lg p-4 mb-4" id="${itemId}">
            <input type="hidden" name="items[${itemCount}][product_id]" value="${productId}">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Product</label>
                    <div class="mt-1 text-sm font-medium">${productName}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Quantity *</label>
                    <input type="number" name="items[${itemCount}][quantity_ordered]" 
                           value="1" min="1" required
                           class="mt-1 quantity-input focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Unit Cost *</label>
                    <input type="number" name="items[${itemCount}][unit_cost]" 
                           value="${productPrice}" step="0.01" min="0" required
                           class="mt-1 cost-input focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>
                <div class="flex items-end">
                    <button type="button" class="remove-item bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('items-container').insertAdjacentHTML('beforeend', itemHtml);
    
    // Add event listeners for the new inputs
    const newItem = document.getElementById(itemId);
    newItem.querySelector('.quantity-input').addEventListener('input', updateTotal);
    newItem.querySelector('.cost-input').addEventListener('input', updateTotal);
    newItem.querySelector('.remove-item').addEventListener('click', function() {
        newItem.remove();
        updateTotal();
    });
    
    itemCount++;
    updateTotal();
}

// Add event listeners to existing items
document.querySelectorAll('.item-row').forEach(row => {
    row.querySelector('.quantity-input').addEventListener('input', updateTotal);
    row.querySelector('.cost-input').addEventListener('input', updateTotal);
    row.querySelector('.remove-item').addEventListener('click', function() {
        row.remove();
        updateTotal();
    });
});

function updateTotal() {
    let total = 0;
    
    document.querySelectorAll('.item-row').forEach(row => {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
        total += quantity * cost;
    });
    
    document.getElementById('total-amount').textContent = '$' + total.toFixed(2);
}

// Close modal when clicking outside
window.addEventListener('click', function(e) {
    const modal = document.getElementById('product-modal');
    if (e.target === modal) {
        modal.classList.add('hidden');
    }
});

// Initialize total on page load
updateTotal();
</script>
@endpush
@endsection