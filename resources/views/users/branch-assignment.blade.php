@extends('layouts.app')

@section('title', 'User Branch Assignment')

@section('breadcrumbs')
<li>
    <span class="text-gray-500">/</span>
    <a href="{{ route('users.index') }}" class="text-gray-500 hover:text-gray-700">Users</a>
</li>
<li>
    <span class="text-gray-500">/</span>
    <span class="text-gray-900">Branch Assignment</span>
</li>
@endsection

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">User Branch Assignment</h1>
                <p class="text-gray-600 mt-1">Assign users to specific branches for access control and reporting</p>
            </div>
            <a href="{{ route('users.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                <i class="fas fa-arrow-left mr-2"></i>Back to Users
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $users->count() }}</div>
                <div class="text-sm text-gray-600">Total Users</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow text-center">
                <div class="text-2xl font-bold text-green-600">{{ $users->where('branch_id', '!=', null)->count() }}</div>
                <div class="text-sm text-gray-600">Assigned to Branches</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $users->where('branch_id', null)->count() }}</div>
                <div class="text-sm text-gray-600">Not Assigned</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $branches->count() }}</div>
                <div class="text-sm text-gray-600">Available Branches</div>
            </div>
        </div>

        <!-- Branch Assignment Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Manage Branch Assignments</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assign Branch</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50 {{ $user->id === auth()->id() ? 'bg-blue-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $user->name }}
                                            @if($user->id === auth()->id())
                                                <span class="ml-2 text-xs text-blue-600">(You)</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($user->role === 'admin') bg-red-100 text-red-800
                                        @elseif($user->role === 'manager') bg-purple-100 text-purple-800
                                        @elseif($user->role === 'accountant') bg-green-100 text-green-800
                                        @else bg-blue-100 text-blue-800
                                        @endif">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($user->branch)
                                        <div class="flex items-center">
                                            <i class="fas fa-store mr-2 text-gray-400"></i>
                                            <span class="font-medium">{{ $user->branch->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-orange-600 font-medium">Not Assigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form action="{{ route('users.assign-branch', $user) }}" method="POST" class="flex space-x-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="branch_id" class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 
                                            {{ $user->id === auth()->id() ? 'bg-blue-100' : '' }}"
                                            {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                            <option value="">Select Branch</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" 
                                                    {{ $user->branch_id == $branch->id ? 'selected' : '' }}
                                                    {{ $user->id === auth()->id() && $user->branch_id == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm transition-colors
                                            {{ $user->id === auth()->id() ? 'bg-gray-400 cursor-not-allowed' : '' }}"
                                            {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                            Assign
                                        </button>
                                    </form>
                                    @if($user->id === auth()->id())
                                        <p class="text-xs text-blue-600 mt-1">You cannot change your own branch</p>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Instructions -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Branch Assignment Guide</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Assigning users to branches restricts their access to data from specific locations</li>
                            <li>Users without branch assignments can access data from all branches</li>
                            <li>You cannot change your own branch assignment for security reasons</li>
                            <li>Branch assignments affect POS access, inventory visibility, and reporting</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection