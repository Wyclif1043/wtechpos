@extends('layouts.app')

@section('page-title', 'Point of Sale')

@section('content')
<div class="h-screen flex flex-col bg-gray-50">
    <!-- Professional Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-ocean-600 to-cyan-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cash-register text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Point of Sale</h1>
                        <p class="text-sm text-gray-600">Welcome back, {{ auth()->user()->name }}</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Shift Status -->
                    <div class="flex items-center space-x-3">
                        @if(auth()->user()->hasActiveShift())
                        <div class="flex items-center bg-emerald-50 text-emerald-700 px-4 py-2 rounded-lg border border-emerald-200">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></div>
                            <span class="text-sm font-medium">Shift Active</span>
                        </div>
                        @else
                        <div class="flex items-center bg-red-50 text-red-700 px-4 py-2 rounded-lg border border-red-200">
                            <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                            <span class="text-sm font-medium">No Active Shift</span>
                        </div>
                        @endif
                    </div>
                    
                    <a href="{{ route('dashboard') }}" 
                       class="bg-gray-600 text-white px-6 py-2.5 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Products Panel -->
        <div class="w-2/3 bg-white border-r border-gray-200 overflow-hidden flex flex-col">
            <!-- Search & Actions Bar -->
            <div class="bg-gray-50 border-b border-gray-200 p-6">
                <div class="flex space-x-4 mb-4">
                    <!-- Quick Search -->
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   id="quickSearch" 
                                   placeholder="Search products by name, barcode, or SKU..."
                                   class="w-full pl-12 pr-4 py-3.5 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 text-gray-900 placeholder-gray-500">
                        </div>
                    </div>
                    
                    <!-- Barcode Input -->
                    <div class="w-80">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-barcode text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   id="barcodeInput" 
                                   placeholder="Scan barcode..."
                                   class="w-full pl-12 pr-4 py-3.5 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 font-mono placeholder-gray-500"
                                   autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="flex-1 overflow-y-auto p-6">
                @if($products->count() > 0)
                    @foreach($products as $category => $categoryProducts)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-3 border-b border-gray-200">{{ $category }}</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            @foreach($categoryProducts as $product)
                            <div class="bg-white border border-gray-200 rounded-xl p-4 hover:shadow-lg transition-all duration-200 cursor-pointer product-card group"
                                 data-product-id="{{ $product->id }}"
                                 data-product-name="{{ $product->name }}"
                                 data-product-price="{{ $product->selling_price }}"
                                 data-product-stock="{{ $product->stock_quantity }}">
                                <div class="flex flex-col h-full">
                                    <!-- Product Image -->
                                    <div class="bg-gray-50 rounded-lg h-28 mb-3 flex items-center justify-center group-hover:bg-gray-100 transition-colors">
                                        @if($product->image)
                                            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="h-24 w-24 object-cover rounded">
                                        @else
                                            <i class="fas fa-box text-gray-400 text-3xl"></i>
                                        @endif
                                    </div>
                                    
                                    <!-- Product Info -->
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900 text-sm leading-tight mb-2">{{ $product->name }}</h4>
                                        <p class="text-ocean-600 font-bold text-lg mb-3">${{ number_format($product->selling_price, 2) }}</p>
                                        
                                        <!-- Stock Status -->
                                        <div class="mb-3">
                                            @if($product->stock_quantity <= 0)
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs bg-red-100 text-red-800 rounded-full border border-red-200">
                                                    <i class="fas fa-times mr-1 text-xs"></i>
                                                    Out of Stock
                                                </span>
                                            @elseif($product->stock_quantity <= $product->min_stock)
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs bg-amber-100 text-amber-800 rounded-full border border-amber-200">
                                                    <i class="fas fa-exclamation-triangle mr-1 text-xs"></i>
                                                    Low Stock
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs bg-emerald-100 text-emerald-800 rounded-full border border-emerald-200">
                                                    <i class="fas fa-check mr-1 text-xs"></i>
                                                    In Stock
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Add to Cart Button -->
                                    <button class="w-full bg-gradient-to-br from-ocean-600 to-cyan-600 hover:from-ocean-700 hover:to-cyan-700 text-white py-2.5 rounded-lg text-sm font-medium transition-all duration-200 add-to-cart-btn disabled:from-gray-400 disabled:to-gray-400 disabled:cursor-not-allowed"
                                            {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-cart-plus mr-2"></i>
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-16">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-box-open text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Products Available</h3>
                        <p class="text-gray-600 max-w-md mx-auto">Add some products to your inventory to start processing sales.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Cart Panel -->
        <div class="w-1/3 bg-white flex flex-col border-l border-gray-200">
            <!-- Cart Header -->
            <div class="bg-gray-50 border-b border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Current Sale</h2>
                    <div class="text-sm text-gray-500" id="cartItemCount">0 items</div>
                </div>
                
                <!-- Customer Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                    <select id="customerSelect" class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 text-sm">
                        <option value="">Walk-in Customer</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" 
                                data-credit-balance="{{ $customer->credit_balance }}"
                                data-loyalty-points="{{ $customer->loyalty_points }}">
                            {{ $customer->name }} ({{ $customer->phone }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-6">
                <div id="cartItems">
                    <div class="text-center text-gray-500 py-16">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shopping-cart text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-600 font-medium mb-2">Your cart is empty</p>
                        <p class="text-sm text-gray-500">Add products to start a sale</p>
                    </div>
                </div>
            </div>

            <!-- Cart Summary & Actions -->
            <div class="border-t border-gray-200 bg-white">
                <!-- Cart Totals -->
                <div class="p-6 space-y-3 bg-gray-50 border-b border-gray-200">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal:</span>
                        <span id="cartSubtotal" class="font-semibold text-gray-900">$0.00</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax (16%):</span>
                        <span id="cartTax" class="font-semibold text-gray-900">$0.00</span>
                    </div>
                    <div class="flex justify-between text-red-600 text-sm" id="discountRow" style="display: none;">
                        <span class="text-gray-600">Discount:</span>
                        <span id="cartDiscount" class="font-semibold">-$0.00</span>
                    </div>
                    <div class="flex justify-between font-bold text-lg border-t border-gray-300 pt-3 mt-2">
                        <span class="text-gray-900">Total:</span>
                        <span id="cartTotal" class="text-ocean-600">$0.00</span>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="p-6 border-b border-gray-200">
                    <div class="grid grid-cols-2 gap-3">
                        <button id="holdSaleBtn" 
                                class="bg-amber-500 text-white py-3 px-4 rounded-lg text-sm font-medium hover:bg-amber-600 transition-all duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <i class="fas fa-pause mr-2"></i>
                            Hold Sale
                        </button>
                        <button id="viewHeldSalesBtn" 
                                class="bg-purple-500 text-white py-3 px-4 rounded-lg text-sm font-medium hover:bg-purple-600 transition-all duration-200">
                            <i class="fas fa-list mr-2"></i>
                            View Held
                        </button>
                    </div>
                </div>

                <!-- Payment Section -->
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment</h3>
                    
                    <!-- Payment Methods -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Method</label>
                                <select id="paymentMethod" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="credit">Credit</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                                <input type="number" id="paymentAmount" step="0.01" min="0.01"
                                       class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"
                                       placeholder="0.00">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reference (Optional)</label>
                            <input type="text" id="paymentReference" 
                                   class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"
                                   placeholder="Transaction ID, etc.">
                        </div>
                        
                        <div class="flex space-x-3 pt-2">
                            <button id="addPaymentBtn" 
                                    class="flex-1 bg-ocean-600 text-white py-3.5 px-4 rounded-lg text-sm font-medium hover:bg-ocean-700 transition-all duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Add Payment
                            </button>
                            <button id="completeSaleBtn" 
                                    class="flex-1 bg-gradient-to-br from-emerald-500 to-green-600 text-white py-3.5 px-4 rounded-lg text-sm font-medium hover:from-emerald-600 hover:to-green-700 transition-all duration-200 disabled:from-gray-400 disabled:to-gray-400 disabled:cursor-not-allowed">
                                <i class="fas fa-check-circle mr-2"></i>
                                Complete Sale
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
        <div class="text-center">
            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check-circle text-emerald-500 text-3xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Sale Completed!</h3>
            <p class="text-gray-600 mb-3">Sale #<span id="saleNumber" class="font-mono text-ocean-600">SALE-1234</span></p>
            <p class="text-gray-600 mb-6">Change: <span id="changeDisplay" class="font-semibold text-gray-900">$0.00</span></p>
            <button onclick="closeSuccessModal()" 
                    class="w-full bg-ocean-600 text-white py-3.5 px-6 rounded-lg text-sm font-medium hover:bg-ocean-700 transition-all duration-200">
                Continue Selling
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.product-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.cart-item {
    transition: all 0.2s ease-in-out;
}

.cart-item:hover {
    background-color: #f8fafc;
}

/* Custom scrollbar styling */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Smooth focus transitions */
input, select, button {
    transition: all 0.2s ease-in-out;
}
</style>
@endpush

@push('scripts')
<script>
// Keep your existing JavaScript functionality exactly as it was
// The design changes are purely visual and won't affect functionality
document.addEventListener('DOMContentLoaded', function() {
    // Your existing JavaScript code here
    // All event listeners and functionality remain unchanged
});
</script>
@endpush