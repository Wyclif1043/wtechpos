@extends('layouts.app')

@section('title', 'Product Transfers')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Product Transfers</h3>
            <a href="{{ route('product-movements.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-exchange-alt mr-2"></i> New Transfer
            </a>
        </div>
        
        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filters -->
            <div class="mb-6 bg-gray-50 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" onchange="window.location.href = this.value ? '{{ route('product-movements.index') }}?status=' + this.value : '{{ route('product-movements.index') }}'">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Branch</label>
                        <select class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" onchange="window.location.href = this.value ? '{{ route('product-movements.index') }}?from_branch=' + this.value : '{{ route('product-movements.index') }}'">
                            <option value="">All Branches</option>
                            @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" {{ request('from_branch') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" placeholder="Reference or Product..." class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                               onkeyup="if(event.keyCode===13) window.location.href = '{{ route('product-movements.index') }}?search=' + this.value">
                    </div>
                    <div class="flex items-end">
                        <a href="{{ route('product-movements.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors w-full text-center">
                            Clear Filters
                        </a>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From → To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($movements as $movement)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $movement->reference_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $movement->product->name }}<br>
                                    <small class="text-gray-400">SKU: {{ $movement->product->sku }}</small>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <span class="font-medium">{{ $movement->fromBranch->name }}</span>
                                        <i class="fas fa-arrow-right mx-2 text-gray-400 text-xs"></i>
                                        <span class="font-medium">{{ $movement->toBranch->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $movement->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $movement->status_badge }}">
                                        {{ ucfirst($movement->status) }}
                                    </span>
                                    @if($movement->tracking_number)
                                        <br><small class="text-gray-400">Track: {{ $movement->tracking_number }}</small>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $movement->requestedBy->name }}<br>
                                    <small class="text-gray-400">{{ $movement->created_at->format('M d, Y') }}</small>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($movement->delivered_at)
                                        Delivered: {{ $movement->delivered_at->format('M d, Y') }}
                                    @elseif($movement->shipped_at)
                                        Shipped: {{ $movement->shipped_at->format('M d, Y') }}
                                    @elseif($movement->approved_at)
                                        Approved: {{ $movement->approved_at->format('M d, Y') }}
                                    @else
                                        Requested: {{ $movement->created_at->format('M d, Y') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('product-movements.show', $movement) }}" class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 p-2 rounded transition-colors" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($movement->status === 'pending')
                                        <form action="{{ route('product-movements.approve', $movement) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900 bg-green-100 hover:bg-green-200 p-2 rounded transition-colors" title="Approve Transfer">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('product-movements.reject', $movement) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 p-2 rounded transition-colors" title="Reject Transfer" onclick="return confirm('Are you sure you want to reject this transfer?')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        @endif

                                        @if($movement->status === 'approved')
                                        <button onclick="showShipModal({{ $movement->id }})" class="text-purple-600 hover:text-purple-900 bg-purple-100 hover:bg-purple-200 p-2 rounded transition-colors" title="Mark as Shipped">
                                            <i class="fas fa-shipping-fast"></i>
                                        </button>
                                        @endif

                                        @if($movement->status === 'shipped' && auth()->user()->branch_id == $movement->to_branch_id)
                                        <form action="{{ route('product-movements.receive', $movement) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900 bg-green-100 hover:bg-green-200 p-2 rounded transition-colors" title="Receive Product" onclick="return confirm('Confirm receipt of this product? This will add stock to your branch.')">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                    <i class="fas fa-inbox text-4xl text-gray-300 mb-2"></i><br>
                                    No transfer requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Ship Modal -->
<div id="shipModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Mark Transfer as Shipped</h3>
            <form id="shipForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="tracking_number" class="block text-sm font-medium text-gray-700 mb-2">Tracking Number (Optional)</label>
                    <input type="text" name="tracking_number" id="tracking_number" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Enter tracking number">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeShipModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md transition-colors">
                        Mark as Shipped
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showShipModal(movementId) {
    const form = document.getElementById('shipForm');
    form.action = `/product-movements/${movementId}/ship`;
    document.getElementById('shipModal').classList.remove('hidden');
}

function closeShipModal() {
    document.getElementById('shipModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('shipModal').addEventListener('click', function(e) {
    if (e.target.id === 'shipModal') {
        closeShipModal();
    }
});
</script>
@endpush
@endsection