@extends('layouts.app')

@section('page-title', 'Create Warranty')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create New Warranty</h1>
                <p class="text-gray-600 mt-2">Register a new warranty for a product sale</p>
            </div>
            <a href="{{ route('warranties.index') }}" 
               class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Warranties
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-soft border border-gray-200 overflow-hidden">
            <form action="{{ route('warranties.store') }}" method="POST">
                @csrf
                
                <!-- Card Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Warranty Information</h3>
                    <p class="text-sm text-gray-600 mt-1">Fill in the details to create a new warranty</p>
                </div>

                <!-- Card Body -->
                <div class="p-6 space-y-6">
                    <!-- Sale Selection Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Sale Selection -->
                        <div>
                            <label for="sale_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Sale *
                            </label>
                            <select name="sale_id" id="sale_id" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200 bg-white"
                                    required>
                                <option value="">Select a sale</option>
                                @foreach($sales as $sale)
                                <option value="{{ $sale->id }}">
                                    {{ $sale->sale_number }} - 
                                    {{ $sale->customer->name ?? 'No Customer' }} 
                                    ({{ $sale->created_at->format('M d, Y') }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Product Item Selection -->
                        <div>
                            <label for="sale_item_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Product Item *
                            </label>
                            <select name="sale_item_id" id="sale_item_id" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200 bg-gray-100"
                                    required disabled>
                                <option value="">Select sale first</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-2" id="item-help">
                                Only items that haven't been returned and don't have existing warranties are shown.
                            </p>
                        </div>
                    </div>

                    <!-- Warranty Details Section -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Warranty Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                Warranty Type *
                            </label>
                            <select name="type" id="type" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200 bg-white"
                                    required>
                                <option value="">Select type</option>
                                @foreach($warrantyTypes as $key => $type)
                                <option value="{{ $key }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Duration -->
                        <div>
                            <label for="duration_months" class="block text-sm font-medium text-gray-700 mb-2">
                                Duration (Months) *
                            </label>
                            <input type="number" name="duration_months" id="duration_months" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                                   min="1" required placeholder="e.g., 12">
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Start Date *
                            </label>
                            <input type="date" name="start_date" id="start_date" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- Product Identification Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Serial Number -->
                        <div>
                            <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Serial Number
                            </label>
                            <input type="text" name="serial_number" id="serial_number" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                                   placeholder="Enter product serial number">
                        </div>

                        <!-- Batch Number -->
                        <div>
                            <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Batch Number
                            </label>
                            <input type="text" name="batch_number" id="batch_number" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                                   placeholder="Enter product batch number">
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    <div>
                        <label for="terms" class="block text-sm font-medium text-gray-700 mb-2">
                            Terms & Conditions
                        </label>
                        <textarea name="terms" id="terms" rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                                  placeholder="Enter warranty terms and conditions..."></textarea>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Additional Notes
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                                  placeholder="Any additional notes or special instructions..."></textarea>
                    </div>
                </div>

                <!-- Card Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                        <a href="{{ route('warranties.index') }}" 
                           class="text-gray-600 hover:text-gray-900 font-medium transition-colors duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Cancel
                        </a>
                        
                        <button type="submit" 
                                class="w-full sm:w-auto bg-gradient-to-br from-ocean-600 to-cyan-600 text-white px-8 py-3 rounded-lg hover:from-ocean-700 hover:to-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ocean-500 transition-all duration-200 transform hover:scale-105 font-medium">
                            <i class="fas fa-save mr-2"></i>
                            Create Warranty
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.shadow-soft {
    box-shadow: 0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04);
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const saleSelect = document.getElementById('sale_id');
    const itemSelect = document.getElementById('sale_item_id');

    saleSelect.addEventListener('change', function() {
        const saleId = this.value;
        
        if (saleId) {
            itemSelect.disabled = false;
            itemSelect.classList.remove('bg-gray-100');
            itemSelect.classList.add('bg-white');
            
            // Show loading state
            itemSelect.innerHTML = '<option value="">Loading items...</option>';
            
            fetch(`/sales/${saleId}/items`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    itemSelect.innerHTML = '<option value="">Select product item</option>';
                    
                    if (data.items && data.items.length > 0) {
                        let eligibleItems = 0;
                        
                        data.items.forEach(function(item) {
                            if (item.can_create_warranty) {
                                const optionText = `${item.product_name} - Qty: ${item.quantity} (${item.quantity_remaining} remaining) - $${item.unit_price}`;
                                const option = document.createElement('option');
                                option.value = item.id;
                                option.textContent = optionText;
                                itemSelect.appendChild(option);
                                eligibleItems++;
                            }
                        });
                        
                        if (eligibleItems === 0) {
                            const option = document.createElement('option');
                            option.value = "";
                            option.textContent = "No eligible items found";
                            option.disabled = true;
                            itemSelect.appendChild(option);
                        }
                    } else {
                        const option = document.createElement('option');
                        option.value = "";
                        option.textContent = "No items available";
                        option.disabled = true;
                        itemSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Error fetching sale items:', error);
                    itemSelect.innerHTML = '<option value="">Error loading items</option>';
                });
        } else {
            itemSelect.disabled = true;
            itemSelect.classList.remove('bg-white');
            itemSelect.classList.add('bg-gray-100');
            itemSelect.innerHTML = '<option value="">Select sale first</option>';
        }
    });

    // Set default start date to today
    const startDateInput = document.getElementById('start_date');
    startDateInput.value = new Date().toISOString().split('T')[0];
});
</script>
@endpush