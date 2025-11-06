@extends('layouts.app')

@section('page-title', 'Create Warranty Claim')

@section('content')
<div class="max-w-6xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create Warranty Claim</h1>
                <p class="text-gray-600 mt-2">Submit a new warranty claim for a product</p>
            </div>
            <a href="{{ route('warranty-claims.index') }}" 
               class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Claims
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-soft border border-gray-200 p-8">
            <form action="{{ route('warranty-claims.store') }}" method="POST">
                @csrf
                
                <!-- Sale Selection Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
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
                                {{ $sale->sale_number }} - {{ $sale->customer->name }} ({{ $sale->created_at->format('M d, Y') }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="sale_item_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Product Item *
                        </label>
                        <select name="sale_item_id" id="sale_item_id" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200 bg-gray-100"
                                required disabled>
                            <option value="">Select sale first</option>
                        </select>
                    </div>
                </div>

                <!-- Warranty & Date Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label for="product_warranty_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Warranty *
                        </label>
                        <select name="product_warranty_id" id="product_warranty_id" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200 bg-gray-100"
                                required disabled>
                            <option value="">Select product item first</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="claim_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Claim Date *
                        </label>
                        <input type="date" name="claim_date" id="claim_date" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <!-- Issue Type Section -->
                <div class="mb-8">
                    <label for="issue_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Issue Type *
                    </label>
                    <select name="issue_type" id="issue_type" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200 bg-white"
                            required>
                        <option value="">Select issue type</option>
                        @foreach($issueTypes as $key => $type)
                        <option value="{{ $key }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Problem Description -->
                <div class="mb-8">
                    <label for="problem_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Problem Description *
                    </label>
                    <textarea name="problem_description" id="problem_description" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                              rows="5"
                              placeholder="Please describe the problem in detail, including when it started, what symptoms you're experiencing, and any troubleshooting steps you've already taken..."
                              required></textarea>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-xs text-gray-500">Minimum 10 characters required</span>
                        <span id="char-count" class="text-xs text-gray-500">0 characters</span>
                    </div>
                </div>

                <!-- Warranty Details Preview -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8 hidden" id="warranty-details">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-blue-900">Warranty Details</h3>
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shield-alt text-blue-600"></i>
                        </div>
                    </div>
                    <div id="warranty-info" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0 pt-6 border-t border-gray-200">
                    <a href="{{ route('warranty-claims.index') }}" 
                       class="w-full sm:w-auto bg-gray-200 text-gray-700 px-8 py-3.5 rounded-lg hover:bg-gray-300 transition-all duration-200 font-medium text-center">
                        Cancel
                    </a>
                    
                    <button type="submit" 
                            class="w-full sm:w-auto bg-gradient-to-br from-ocean-600 to-cyan-600 text-white px-8 py-3.5 rounded-lg hover:from-ocean-700 hover:to-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ocean-500 transition-all duration-200 transform hover:scale-105 font-medium shadow-md">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Submit Claim
                    </button>
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
    const warrantySelect = document.getElementById('product_warranty_id');
    const warrantyDetails = document.getElementById('warranty-details');
    const warrantyInfo = document.getElementById('warranty-info');
    const problemDescription = document.getElementById('problem_description');
    const charCount = document.getElementById('char-count');

    // Character count for problem description
    problemDescription.addEventListener('input', function() {
        charCount.textContent = `${this.value.length} characters`;
        
        if (this.value.length < 10) {
            charCount.classList.add('text-red-500');
            charCount.classList.remove('text-gray-500');
        } else {
            charCount.classList.remove('text-red-500');
            charCount.classList.add('text-gray-500');
        }
    });

    saleSelect.addEventListener('change', function() {
        const saleId = this.value;
        
        if (saleId) {
            // Enable and reset dependent fields
            itemSelect.disabled = false;
            itemSelect.className = itemSelect.className.replace('bg-gray-100', 'bg-white');
            warrantySelect.disabled = true;
            warrantySelect.className = warrantySelect.className.replace('bg-white', 'bg-gray-100');
            warrantyDetails.classList.add('hidden');
            
            // Show loading state
            itemSelect.innerHTML = '<option value="">Loading items...</option>';
            
            fetch(`/sales/${saleId}/warranty-items`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    itemSelect.innerHTML = '<option value="">Select product item</option>';
                    
                    if (data.items && data.items.length > 0) {
                        data.items.forEach(function(item) {
                            if (item.can_claim) {
                                const option = document.createElement('option');
                                option.value = item.id;
                                option.textContent = `${item.product_name} - Qty: ${item.quantity} (${item.quantity_remaining} remaining)`;
                                option.setAttribute('data-warranties', JSON.stringify(item.warranties));
                                itemSelect.appendChild(option);
                            }
                        });
                    } else {
                        const option = document.createElement('option');
                        option.value = "";
                        option.textContent = "No eligible items found";
                        option.disabled = true;
                        itemSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Error fetching sale items:', error);
                    itemSelect.innerHTML = '<option value="">Error loading items</option>';
                });
        } else {
            // Reset all dependent fields
            itemSelect.disabled = true;
            itemSelect.className = itemSelect.className.replace('bg-white', 'bg-gray-100');
            itemSelect.innerHTML = '<option value="">Select sale first</option>';
            warrantySelect.disabled = true;
            warrantySelect.className = warrantySelect.className.replace('bg-white', 'bg-gray-100');
            warrantySelect.innerHTML = '<option value="">Select product item first</option>';
            warrantyDetails.classList.add('hidden');
        }
    });

    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const warranties = selectedOption.getAttribute('data-warranties') ? 
                          JSON.parse(selectedOption.getAttribute('data-warranties')) : [];
        
        warrantySelect.innerHTML = '<option value="">Select warranty</option>';
        warrantyDetails.classList.add('hidden');
        
        if (warranties.length > 0) {
            warrantySelect.disabled = false;
            warrantySelect.className = warrantySelect.className.replace('bg-gray-100', 'bg-white');
            
            warranties.forEach(function(warranty) {
                if (warranty.is_under_warranty) {
                    const option = document.createElement('option');
                    option.value = warranty.id;
                    option.textContent = `${warranty.name} (${warranty.type} - ${warranty.duration})`;
                    option.setAttribute('data-warranty-details', JSON.stringify(warranty));
                    warrantySelect.appendChild(option);
                }
            });

            if (warrantySelect.options.length === 1) {
                const option = document.createElement('option');
                option.value = "";
                option.textContent = "No active warranties found";
                option.disabled = true;
                warrantySelect.appendChild(option);
            }
        } else {
            warrantySelect.disabled = true;
            warrantySelect.className = warrantySelect.className.replace('bg-white', 'bg-gray-100');
            warrantySelect.innerHTML = '<option value="">No warranties available</option>';
        }
    });

    warrantySelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const warrantyDetailsData = selectedOption.getAttribute('data-warranty-details');
        
        if (warrantyDetailsData) {
            const warranty = JSON.parse(warrantyDetailsData);
            warrantyInfo.innerHTML = `
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium text-blue-800">Warranty Name:</span>
                        <span class="text-blue-900">${warranty.name}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-blue-800">Type:</span>
                        <span class="text-blue-900">${warranty.type}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-blue-800">Duration:</span>
                        <span class="text-blue-900">${warranty.duration}</span>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium text-blue-800">End Date:</span>
                        <span class="text-blue-900">${warranty.end_date}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-blue-800">Status:</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                            Under Warranty
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-blue-800">Remaining:</span>
                        <span class="text-blue-900 font-semibold">${warranty.remaining_days} days</span>
                    </div>
                </div>
            `;
            warrantyDetails.classList.remove('hidden');
        } else {
            warrantyDetails.classList.add('hidden');
        }
    });

    // Set default claim date to today
    document.getElementById('claim_date').value = new Date().toISOString().split('T')[0];
});
</script>
@endpush