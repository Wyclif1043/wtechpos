@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Edit Customer</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Update customer information below.
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form action="{{ route('customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <!-- Basic Information -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                                
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                                        <input type="text" name="name" id="name" required
                                               value="{{ old('name', $customer->name) }}"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                            <input type="email" name="email" id="email"
                                                   value="{{ old('email', $customer->email) }}"
                                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            @error('email')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                            <input type="text" name="phone" id="phone"
                                                   value="{{ old('phone', $customer->phone) }}"
                                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            @error('phone')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div>
                                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                        <textarea name="address" id="address" rows="3"
                                                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('address', $customer->address) }}</textarea>
                                        @error('address')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Read-only Loyalty Information -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-lg font-medium text-gray-900 mb-2">Loyalty Information</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">Loyalty Points:</span>
                                        <span class="font-medium ml-2">{{ number_format($customer->loyalty_points) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Credit Balance:</span>
                                        <span class="font-medium ml-2">${{ number_format($customer->credit_balance, 2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Loyalty Tier:</span>
                                        <span class="font-medium ml-2">{{ $customer->loyalty_tier }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Total Spent:</span>
                                        <span class="font-medium ml-2">${{ number_format($customer->total_spent, 2) }}</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    Loyalty points and credit balance are managed through transactions and cannot be edited directly.
                                </p>
                            </div>
                        </div>

                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <a href="{{ route('customers.show', $customer) }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Update Customer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection