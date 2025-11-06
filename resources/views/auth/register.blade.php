@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto h-16 w-16 bg-gradient-to-br from-ocean-600 to-cyan-600 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-user-plus text-white text-xl"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-900">
                Register New User
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Create a new user account for the POS system
            </p>
        </div>

        <!-- Form -->
        <form class="bg-white rounded-2xl shadow-soft border border-gray-200 p-8" action="{{ route('register') }}" method="POST">
            @csrf
            
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                    </div>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Single Row Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Column 1 -->
                <div class="space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                        <input id="name" name="name" type="text" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                               placeholder="Full name" value="{{ old('name') }}">
                    </div>
                    
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                        <select id="role" name="role" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200 bg-white">
                            <option value="">Select role</option>
                            @foreach($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Column 2 -->
                <div class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input id="email" name="email" type="email" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                               placeholder="Email address" value="{{ old('email') }}">
                    </div>
                    
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                        <select id="branch_id" name="branch_id" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200 bg-white">
                            <option value="">Select branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Column 3 -->
                <div class="space-y-5">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input id="phone" name="phone" type="tel" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                               placeholder="Phone number" value="{{ old('phone') }}">
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                        <input id="password" name="password" type="password" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                               placeholder="Password">
                    </div>
                    
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 transition-colors duration-200"
                               placeholder="Confirm password">
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                <a href="{{ route('login') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 font-medium transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Login
                </a>

                <button type="submit" 
                        class="w-full sm:w-auto flex justify-center items-center py-3.5 px-8 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-br from-ocean-600 to-cyan-600 hover:from-ocean-700 hover:to-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ocean-500 transition-all duration-200 transform hover:scale-105 shadow-md">
                    <i class="fas fa-user-plus mr-2"></i>
                    Register User
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.shadow-soft {
    box-shadow: 0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04);
}
</style>
@endsection