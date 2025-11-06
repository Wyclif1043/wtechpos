@extends('layouts.app')

@section('page-title', 'Create Product Warranty')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create Product Warranty</h1>
                <p class="text-gray-600 mt-2">Add a new warranty template for your products</p>
            </div>
            <a href="{{ route('product-warranties.index') }}" 
               class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Warranties
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-soft border border-gray-200 p-8">
            <form action="{{ route('product-warranties.store') }}" method="POST">
                @csrf
                
                <!-- Product Selection -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="product_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Product *
                        </label>
                        <select name="product_id" id="product_id" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200 bg-white"
                                required>
                            <option value="">Select a product</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} (SKU: {{ $product->sku }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="warranty_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Warranty Name *
                        </label>
                        <input type="text" name="warranty_name" id="warranty_name" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                               placeholder="e.g., Standard 1-Year Warranty"
                               required>
                    </div>
                </div>

                <!-- Warranty Type & Duration -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Warranty Type *
                        </label>
                        <select name="type" id="type" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200 bg-white"
                                required>
                            <option value="">Select warranty type</option>
                            @foreach($warrantyTypes as $key => $type)
                            <option value="{{ $key }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="duration_months" class="block text-sm font-medium text-gray-700 mb-2">
                            Duration (Months) *
                        </label>
                        <input type="number" name="duration_months" id="duration_months" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                               placeholder="e.g., 12"
                               min="1" required>
                        <div id="duration-display" class="text-sm text-ocean-600 font-medium mt-2"></div>
                    </div>
                </div>

                <!-- Coverage Details -->
                <div class="mb-6">
                    <label for="coverage_details" class="block text-sm font-medium text-gray-700 mb-2">
                        Coverage Details
                    </label>
                    <textarea name="coverage_details" id="coverage_details" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                              rows="3"
                              placeholder="Describe what this warranty covers (parts, labor, specific components, etc.)"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Optional: Detailed description of warranty coverage</p>
                </div>

                <!-- Terms & Conditions -->
                <div class="mb-8">
                    <label for="terms" class="block text-sm font-medium text-gray-700 mb-2">
                        Terms & Conditions
                    </label>
                    <textarea name="terms" id="terms" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                              rows="4"
                              placeholder="Enter warranty terms, conditions, limitations, and exclusions..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Optional: Legal terms and conditions for the warranty</p>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0 pt-6 border-t border-gray-200">
                    <a href="{{ route('product-warranties.index') }}" 
                       class="w-full sm:w-auto bg-gray-200 text-gray-700 px-8 py-3.5 rounded-lg hover:bg-gray-300 transition-all duration-200 font-medium text-center">
                        Cancel
                    </a>
                    
                    <button type="submit" 
                            class="w-full sm:w-auto bg-gradient-to-br from-ocean-600 to-cyan-600 text-white px-8 py-3.5 rounded-lg hover:from-ocean-700 hover:to-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ocean-500 transition-all duration-200 transform hover:scale-105 font-medium shadow-md">
                        <i class="fas fa-shield-alt mr-2"></i>
                        Create Warranty Template
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
    const durationInput = document.getElementById('duration_months');
    const durationDisplay = document.getElementById('duration-display');

    function updateDurationDisplay() {
        const months = parseInt(durationInput.value) || 0;
        if (months > 0) {
            if (months >= 12) {
                const years = Math.floor(months / 12);
                const remainingMonths = months % 12;
                let display = `${years} year${years > 1 ? 's' : ''}`;
                if (remainingMonths > 0) {
                    display += ` ${remainingMonths} month${remainingMonths > 1 ? 's' : ''}`;
                }
                durationDisplay.textContent = `Duration: ${display}`;
            } else {
                durationDisplay.textContent = `Duration: ${months} month${months > 1 ? 's' : ''}`;
            }
        } else {
            durationDisplay.textContent = '';
        }
    }

    durationInput.addEventListener('input', updateDurationDisplay);
    updateDurationDisplay(); // Initial call
});
</script>
@endpush