@extends('layouts.app')

@section('page-title', 'Warranty Claims')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Warranty Claims</h1>
                <p class="text-gray-600 mt-2">Manage and track product warranty claims</p>
            </div>
            <a href="{{ route('warranty-claims.create') }}" 
               class="bg-gradient-to-br from-ocean-600 to-cyan-600 text-white px-6 py-3 rounded-lg hover:from-ocean-700 hover:to-cyan-700 transition-all duration-200 font-medium">
                <i class="fas fa-plus mr-2"></i>
                New Claim
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Claims</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-blue-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Pending</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-clock text-amber-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Resolved</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['resolved'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check text-emerald-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Under Warranty</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['under_warranty'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-ocean-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-shield-alt text-ocean-600 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 bg-white">
                        <option value="">All Status</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Issue Type</label>
                    <select name="issue_type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 bg-white">
                        <option value="">All Issue Types</option>
                        <option value="repair" {{ request('issue_type') == 'repair' ? 'selected' : '' }}>Repair</option>
                        <option value="replacement" {{ request('issue_type') == 'replacement' ? 'selected' : '' }}>Replacement</option>
                        <option value="refund" {{ request('issue_type') == 'refund' ? 'selected' : '' }}>Refund</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input type="date" name="date_from" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500" value="{{ request('date_from') }}">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input type="date" name="date_to" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500" value="{{ request('date_to') }}">
                </div>
                
                <div class="flex items-end space-x-3">
                    <button type="submit" class="bg-ocean-600 text-white px-6 py-2.5 rounded-lg hover:bg-ocean-700 transition-colors duration-200 font-medium w-full">
                        Apply Filters
                    </button>
                    <a href="{{ route('warranty-claims.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg hover:bg-gray-300 transition-colors duration-200 font-medium w-full text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Claims Table -->
        <div class="bg-white rounded-2xl shadow-soft border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Claim #</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sale #</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Issue Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Claim Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Warranty Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Claim Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($claims as $claim)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $claim->claim_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $claim->customer->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $claim->product->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('sales.show', $claim->sale_id) }}" class="text-sm text-ocean-600 hover:text-ocean-700 font-medium">
                                    {{ $claim->sale->sale_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst($claim->issue_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $claim->claim_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($claim->is_under_warranty)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        <i class="fas fa-check-circle mr-1 text-xs"></i>
                                        Under Warranty ({{ $claim->warranty_remaining_days }} days)
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1 text-xs"></i>
                                        Warranty Expired
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($claim->status == 'submitted')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Submitted
                                    </span>
                                @elseif($claim->status == 'in_progress')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                        In Progress
                                    </span>
                                @elseif($claim->status == 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        Approved
                                    </span>
                                @elseif($claim->status == 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Completed
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('warranty-claims.show', $claim) }}" 
                                       class="text-ocean-600 hover:text-ocean-700 transition-colors duration-200 p-2 rounded-lg hover:bg-ocean-50"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('warranty-claims.edit', $claim) }}" 
                                       class="text-blue-600 hover:text-blue-700 transition-colors duration-200 p-2 rounded-lg hover:bg-blue-50"
                                       title="Edit Claim">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-clipboard-list text-gray-400 text-xl"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-2">No Warranty Claims Found</h4>
                                <p class="text-gray-600 mb-4">Get started by creating your first warranty claim.</p>
                                <a href="{{ route('warranty-claims.create') }}" 
                                   class="inline-flex items-center text-ocean-600 hover:text-ocean-700 font-medium">
                                    <i class="fas fa-plus mr-2"></i>
                                    Create First Claim
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($claims->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $claims->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.shadow-soft {
    box-shadow: 0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04);
}
</style>
@endsection