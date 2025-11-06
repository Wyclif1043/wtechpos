<!-- resources/views/suppliers/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                        {{ isset($supplier) ? 'Edit Supplier' : 'Create New Supplier' }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Fill in the supplier details below. All fields marked with * are required.
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form action="{{ isset($supplier) ? route('suppliers.update', $supplier) : route('suppliers.store') }}" method="POST">
                    @csrf
                    @if(isset($supplier))
                        @method('PUT')
                    @endif

                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                            <!-- Basic Information -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                                
                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Supplier Name *</label>
                                        <input type="text" name="name" id="name" required
                                               value="{{ old('name', $supplier->name ?? '') }}"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="contact_person" class="block text-sm font-medium text-gray-700">Contact Person</label>
                                        <input type="text" name="contact_person" id="contact_person"
                                               value="{{ old('contact_person', $supplier->contact_person ?? '') }}"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('contact_person')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" name="email" id="email"
                                               value="{{ old('email', $supplier->email ?? '') }}"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('email')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                        <input type="text" name="phone" id="phone"
                                               value="{{ old('phone', $supplier->phone ?? '') }}"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('phone')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Address</h4>
                                
                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                    <textarea name="address" id="address" rows="3"
                                              class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('address', $supplier->address ?? '') }}</textarea>
                                    @error('address')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status (only for edit) -->
                            @if(isset($supplier))
                            <div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="is_active" id="is_active" 
                                           {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}
                                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                        Supplier is active
                                    </label>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <a href="{{ route('suppliers.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ isset($supplier) ? 'Update Supplier' : 'Create Supplier' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection