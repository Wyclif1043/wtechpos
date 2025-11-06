@extends('layouts.app')

@section('title', 'Transfer Details - ' . $productMovement->reference_number)

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Transfer Details</h3>
                <p class="text-sm text-gray-600">Reference: {{ $productMovement->reference_number }}</p>
            </div>
            <div class="flex space-x-2">
                @if($productMovement->document_url)
                <a href="{{ $productMovement->document_url }}" target="_blank" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-file-download mr-2"></i> View Document
                </a>
                @endif
                <a href="{{ route('product-movements.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Transfers
                </a>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Status Timeline -->
            <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Transfer Status</h4>
                <div class="flex items-center justify-between">
                    @php
                        $steps = [
                            'pending' => ['icon' => 'fa-clock', 'color' => 'yellow', 'label' => 'Pending'],
                            'approved' => ['icon' => 'fa-check', 'color' => 'blue', 'label' => 'Approved'],
                            'shipped' => ['icon' => 'fa-shipping-fast', 'color' => 'purple', 'label' => 'Shipped'],
                            'delivered' => ['icon' => 'fa-check-double', 'color' => 'green', 'label' => 'Delivered']
                        ];
                        
                        $currentStep = array_search($productMovement->status, array_keys($steps));
                    @endphp

                    @foreach($steps as $status => $step)
                        @php
                            $isCompleted = array_search($status, array_keys($steps)) <= $currentStep;
                            $isCurrent = $productMovement->status === $status;
                            $color = $isCompleted ? $step['color'] : 'gray';
                        @endphp
                        
                        <div class="flex flex-col items-center flex-1">
                            <div class="flex items-center w-full">
                                @if(!$loop->first)
                                    <div class="flex-1 h-1 {{ $isCompleted ? 'bg-' . $step['color'] . '-500' : 'bg-gray-300' }}"></div>
                                @endif
                                
                                <div class="relative">
                                    <div class="w-10 h-10 rounded-full {{ $isCompleted ? 'bg-' . $step['color'] . '-500' : 'bg-gray-300' }} flex items-center justify-center text-white">
                                        <i class="fas {{ $step['icon'] }}"></i>
                                    </div>
                                    @if($isCurrent)
                                        <div class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 bg-{{ $step['color'] }}-100 text-{{ $step['color'] }}-800 px-2 py-1 rounded text-sm font-medium whitespace-nowrap">
                                            {{ $step['label'] }}
                                        </div>
                                    @endif
                                </div>
                                
                                @if(!$loop->last)
                                    <div class="flex-1 h-1 {{ array_search($status, array_keys($steps)) < $currentStep ? 'bg-' . $step['color'] . '-500' : 'bg-gray-300' }}"></div>
                                @endif
                            </div>
                            <div class="mt-2 text-xs text-gray-500 text-center">
                                @if($status === 'approved' && $productMovement->approved_at)
                                    {{ $productMovement->approved_at->format('M d, Y') }}
                                @elseif($status === 'shipped' && $productMovement->shipped_at)
                                    {{ $productMovement->shipped_at->format('M d, Y') }}
                                @elseif($status === 'delivered' && $productMovement->delivered_at)
                                    {{ $productMovement->delivered_at->format('M d, Y') }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Transfer Information -->
                <div>
                    <h5 class="text-lg font-medium text-gray-900 mb-4">Transfer Information</h5>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-1 gap-3">
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="font-medium text-gray-700">Reference Number:</span>
                                <span class="text-gray-900 font-mono">{{ $productMovement->reference_number }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="font-medium text-gray-700">Product:</span>
                                <span class="text-gray-900">{{ $productMovement->product->name }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="font-medium text-gray-700">SKU:</span>
                                <span class="text-gray-900 font-mono">{{ $productMovement->product->sku }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="font-medium text-gray-700">Quantity:</span>
                                <span class="text-gray-900 font-medium">{{ $productMovement->quantity }}</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="font-medium text-gray-700">Current Status:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $productMovement->status_badge }}">
                                    {{ ucfirst($productMovement->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Branch Information -->
                <div>
                    <h5 class="text-lg font-medium text-gray-900 mb-4">Branch Information</h5>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-1 gap-3">
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="font-medium text-gray-700">From Branch:</span>
                                <span class="text-gray-900">{{ $productMovement->fromBranch->name }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="font-medium text-gray-700">To Branch:</span>
                                <span class="text-gray-900">{{ $productMovement->toBranch->name }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-200">
                                <span class="font-medium text-gray-700">Requested By:</span>
                                <span class="text-gray-900">{{ $productMovement->requestedBy->name }}</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="font-medium text-gray-700">Request Date:</span>
                                <span class="text-gray-900">{{ $productMovement->created_at->format('M d, Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval & Processing Information -->
            @if($productMovement->approved_at || $productMovement->shipped_at || $productMovement->delivered_at)
            <div class="mb-6">
                <h5 class="text-lg font-medium text-gray-900 mb-4">Processing History</h5>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @if($productMovement->approved_at)
                        <div class="text-center p-3 bg-blue-50 rounded-lg">
                            <i class="fas fa-check text-blue-500 text-lg mb-2"></i>
                            <p class="font-medium text-blue-900">Approved</p>
                            <p class="text-sm text-blue-700">{{ $productMovement->approvedBy->name ?? 'N/A' }}</p>
                            <p class="text-xs text-blue-600">{{ $productMovement->approved_at->format('M d, Y H:i') }}</p>
                        </div>
                        @endif

                        @if($productMovement->shipped_at)
                        <div class="text-center p-3 bg-purple-50 rounded-lg">
                            <i class="fas fa-shipping-fast text-purple-500 text-lg mb-2"></i>
                            <p class="font-medium text-purple-900">Shipped</p>
                            <p class="text-sm text-purple-700">{{ $productMovement->processedBy->name ?? 'N/A' }}</p>
                            <p class="text-xs text-purple-600">{{ $productMovement->shipped_at->format('M d, Y H:i') }}</p>
                            @if($productMovement->tracking_number)
                            <p class="text-xs text-purple-600">Track: {{ $productMovement->tracking_number }}</p>
                            @endif
                        </div>
                        @endif

                        @if($productMovement->delivered_at)
                        <div class="text-center p-3 bg-green-50 rounded-lg">
                            <i class="fas fa-check-double text-green-500 text-lg mb-2"></i>
                            <p class="font-medium text-green-900">Delivered</p>
                            <p class="text-sm text-green-700">{{ $productMovement->processedBy->name ?? 'N/A' }}</p>
                            <p class="text-xs text-green-600">{{ $productMovement->delivered_at->format('M d, Y H:i') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Additional Information -->
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <h5 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h5>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="font-medium text-gray-700 block mb-2">Reason for Transfer:</label>
                                <p class="text-gray-900 bg-white p-3 rounded border">{{ $productMovement->reason }}</p>
                            </div>
                            @if($productMovement->notes)
                            <div>
                                <label class="font-medium text-gray-700 block mb-2">Notes:</label>
                                <p class="text-gray-900 bg-white p-3 rounded border">{{ $productMovement->notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
        @if($productMovement->status === 'pending')
        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h6 class="font-medium text-yellow-800 mb-3">Pending Approval</h6>
            <div class="flex space-x-3">
                <form action="{{ route('product-movements.approve', $productMovement) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors flex items-center">
                        <i class="fas fa-check mr-2"></i> Approve Transfer
                    </button>
                </form>
                <form action="{{ route('product-movements.reject', $productMovement) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition-colors flex items-center" onclick="return confirm('Are you sure you want to reject this transfer request?')">
                        <i class="fas fa-times mr-2"></i> Reject Transfer
                    </button>
                </form>
            </div>
        </div>
        @endif

        @if($productMovement->status === 'approved')
        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h6 class="font-medium text-blue-800 mb-3">Ready for Shipping</h6>
            <form action="{{ route('product-movements.ship', $productMovement) }}" method="POST" class="flex items-end space-x-3">
                @csrf
                <div class="flex-1">
                    <label for="tracking_number" class="block text-sm font-medium text-blue-700 mb-2">Tracking Number (Optional)</label>
                    <input type="text" name="tracking_number" id="tracking_number" class="w-full px-3 py-2 border border-blue-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="Enter tracking number">
                </div>
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md transition-colors flex items-center">
                    <i class="fas fa-shipping-fast mr-2"></i> Mark as Shipped
                </button>
            </form>
        </div>
        @endif

        @if($productMovement->status === 'shipped' && auth()->user()->branch_id == $productMovement->to_branch_id)
        <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <h6 class="font-medium text-green-800 mb-3">Product Ready for Receiving</h6>
            <p class="text-green-700 mb-3">The product has been shipped and is ready to be received at your branch.</p>
            <form action="{{ route('product-movements.receive', $productMovement) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors flex items-center" onclick="return confirm('Confirm that you have received this product? This will add stock to your branch inventory.')">
                    <i class="fas fa-check-double mr-2"></i> Confirm Receipt
                </button>
            </form>
        </div>
        @endif
                </div>
    </div>
</div>
@endsection