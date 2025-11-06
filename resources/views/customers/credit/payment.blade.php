@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Record Payment</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Record a payment for {{ $customer->name }}'s credit balance.
                    </p>
                </div>
                <!-- Customer Summary -->
                <div class="mt-6 bg-white p-4 rounded-lg shadow">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Customer Information</h4>
                    <div class="space-y-2 text-sm">
                        <div><strong>Name:</strong> {{ $customer->name }}</div>
                        <div><strong>Current Balance:</strong> 
                            <span class="text-red-600 font-bold">${{ number_format($customer->credit_balance, 2) }}</span>
                        </div>
                        <div><strong>Total Sales:</strong> {{ $customer->sales_count ?? 0 }}</div>
                        <div><strong>Total Spent:</strong> ${{ number_format($customer->total_spent, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form action="{{ route('customers.credit.payment.process', $customer) }}" method="POST">
                    @csrf

                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <!-- Payment Information -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h4>
                                
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label for="amount" class="block text-sm font-medium text-gray-700">Payment Amount *</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <input type="number" name="amount" id="amount" step="0.01" min="0.01" 
                                                   max="{{ $customer->credit_balance }}" required
                                                   value="{{ old('amount', min(100, $customer->credit_balance)) }}"
                                                   class="focus:ring-green-500 focus:border-green-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Maximum payment amount: ${{ number_format($customer->credit_balance, 2) }}
                                        </p>
                                        @error('amount')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700">Payment Notes</label>
                                        <textarea name="notes" id="notes" rows="3"
                                                  placeholder="Optional notes about this payment..."
                                                  class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Preview -->
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-green-900 mb-2">Payment Preview</h4>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-green-700">Current Balance:</span>
                                        <span class="font-medium ml-2">${{ number_format($customer->credit_balance, 2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-green-700">Payment Amount:</span>
                                        <span id="preview-amount" class="font-medium ml-2">$0.00</span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-green-700">New Balance:</span>
                                        <span id="preview-balance" class="font-bold text-lg ml-2">${{ number_format($customer->credit_balance, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <a href="{{ route('customers.credit.show', $customer) }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Process Payment
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
    const amountInput = document.getElementById('amount');
    const previewAmount = document.getElementById('preview-amount');
    const previewBalance = document.getElementById('preview-balance');
    const currentBalance = {{ $customer->credit_balance }};

    function updatePreview() {
        const paymentAmount = parseFloat(amountInput.value) || 0;
        const newBalance = currentBalance - paymentAmount;

        previewAmount.textContent = '$' + paymentAmount.toFixed(2);
        previewBalance.textContent = '$' + newBalance.toFixed(2);
        
        // Update color based on new balance
        if (newBalance <= 0) {
            previewBalance.classList.remove('text-red-600');
            previewBalance.classList.add('text-green-600');
        } else {
            previewBalance.classList.remove('text-green-600');
            previewBalance.classList.add('text-red-600');
        }
    }

    amountInput.addEventListener('input', updatePreview);
    
    // Initial preview update
    updatePreview();
});
</script>
@endpush
@endsection