@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Purchase Order: {{ $purchaseOrder->po_number }}</h1>
                <p class="text-sm text-gray-600">Created on {{ $purchaseOrder->created_at->format('M d, Y') }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('purchase-orders.index') }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Back to List
                </a>
                @if($purchaseOrder->status == 'pending')
                <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" 
                   class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Edit
                </a>
                @endif
            </div>
        </div>

        <!-- Status Alert -->
        <div class="mb-6">
            @if($purchaseOrder->status == 'pending')
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            This purchase order is <strong>pending</strong> and awaiting delivery.
                        </p>
                    </div>
                </div>
            </div>
            @elseif($purchaseOrder->status == 'partially_received')
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            This purchase order has been <strong>partially received</strong>.
                        </p>
                    </div>
                </div>
            </div>
            @elseif($purchaseOrder->status == 'received')
            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            This purchase order has been <strong>fully received</strong>.
                        </p>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            This purchase order has been <strong>cancelled</strong>.
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Order Details -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Order Details</h3>
                    </div>
                    <div class="border-t border-gray-200">
                        <dl>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Supplier</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $purchaseOrder->supplier->name }}
                                    @if($purchaseOrder->supplier->contact_person)
                                    <br><span class="text-gray-600">Contact: {{ $purchaseOrder->supplier->contact_person }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Order Date</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $purchaseOrder->order_date->format('M d, Y') }}
                                </dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Expected Delivery</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('M d, Y') : 'Not specified' }}
                                </dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-bold">
                                    ${{ number_format($purchaseOrder->total_amount, 2) }}
                                </dd>
                            </div>
                            @if($purchaseOrder->notes)
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $purchaseOrder->notes }}
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Order Items</h3>
                    </div>
                    <div class="border-t border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-sm text-gray-500">SKU: {{ $item->product->sku }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->quantity_ordered }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($item->unit_cost, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($item->total_cost, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item->quantity_received > 0)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ $item->quantity_received }}
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                0
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Order Status</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5">
                        <div class="text-center">
                            <div class="text-2xl font-bold mb-2
                                @if($purchaseOrder->status == 'pending') text-yellow-600
                                @elseif($purchaseOrder->status == 'partially_received') text-blue-600
                                @elseif($purchaseOrder->status == 'received') text-green-600
                                @else text-red-600 @endif">
                                {{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }}
                            </div>
                            @if($purchaseOrder->received_date)
                            <div class="text-sm text-gray-600">
                                Received on {{ $purchaseOrder->received_date->format('M d, Y') }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Actions</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 space-y-2">
                        @if(in_array($purchaseOrder->status, ['pending', 'partially_received']))
                        <a href="#receive-section" 
                           class="w-full bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 flex items-center justify-center">
                            <i class="fas fa-truck-loading mr-2"></i>
                            Receive Items
                        </a>
                        @endif

                        @if($purchaseOrder->status == 'pending')
                        <form action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" method="POST" class="inline-block w-full">
                            @csrf
                            @method('POST')
                            <button type="submit" 
                                    onclick="return confirm('Are you sure you want to cancel this purchase order?')"
                                    class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 flex items-center justify-center">
                                <i class="fas fa-times mr-2"></i>
                                Cancel Order
                            </button>
                        </form>
                        @endif

                        <a href="{{ route('purchase-orders.index') }}" 
                           class="w-full bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 flex items-center justify-center">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to List
                        </a>
                    </div>
                </div>

                <!-- PDF Generation Section -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Documents & Reports</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Generate and download purchase-related documents.
                        </p>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5">
                        <div class="grid grid-cols-2 gap-4">
                            <a href="{{ route('purchase-orders.pdf', $purchaseOrder) }}" 
                               target="_blank"
                               class="bg-blue-500 text-white px-4 py-3 rounded hover:bg-blue-600 text-center flex flex-col items-center">
                                <i class="fas fa-file-invoice text-xl mb-2"></i>
                                <span class="text-sm">Purchase Order</span>
                            </a>
                            
                            <a href="{{ route('purchase-orders.delivery-note-pdf', $purchaseOrder) }}" 
                               target="_blank"
                               class="bg-green-500 text-white px-4 py-3 rounded hover:bg-green-600 text-center flex flex-col items-center">
                                <i class="fas fa-truck text-xl mb-2"></i>
                                <span class="text-sm">Delivery Note</span>
                            </a>
                            
                            <a href="{{ route('purchase-orders.grn-pdf', $purchaseOrder) }}" 
                               target="_blank"
                               class="bg-purple-500 text-white px-4 py-3 rounded hover:bg-purple-600 text-center flex flex-col items-center">
                                <i class="fas fa-clipboard-check text-xl mb-2"></i>
                                <span class="text-sm">GRN</span>
                            </a>
                            
                            <a href="{{ route('purchase-orders.invoice-pdf', $purchaseOrder) }}" 
                               target="_blank"
                               class="bg-orange-500 text-white px-4 py-3 rounded hover:bg-orange-600 text-center flex flex-col items-center">
                                <i class="fas fa-file-invoice-dollar text-xl mb-2"></i>
                                <span class="text-sm">Supplier Invoice</span>
                            </a>
                        </div>
                        
                        <!-- Payment Documents Form -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-4">Generate Payment Documents</h4>
                            <form action="{{ route('purchase-orders.payment-voucher-pdf', $purchaseOrder) }}" method="POST" target="_blank" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Date</label>
                                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                        <select name="payment_method" required
                                                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                                            <option value="Bank Transfer">Bank Transfer</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Check">Check</option>
                                            <option value="Credit Card">Credit Card</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                                        <input type="number" name="amount_paid" value="{{ $purchaseOrder->total_amount }}" step="0.01" min="0" required
                                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Reference No</label>
                                        <input type="text" name="reference_number" placeholder="Optional"
                                               class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button type="submit" 
                                            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 flex items-center">
                                        <i class="fas fa-file-invoice mr-2"></i>
                                        Payment Voucher
                                    </button>
                                    <button type="submit" formaction="{{ route('purchase-orders.payment-receipt-pdf', $purchaseOrder) }}" 
                                            class="bg-teal-500 text-white px-4 py-2 rounded hover:bg-teal-600 flex items-center">
                                        <i class="fas fa-receipt mr-2"></i>
                                        Payment Receipt
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receive Items Section -->
        @if(in_array($purchaseOrder->status, ['pending', 'partially_received']))
        <div id="receive-section" class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Receive Items</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Enter the quantities received for each item.
                </p>
            </div>
            <div class="border-t border-gray-200">
                <form action="{{ route('purchase-orders.receive', $purchaseOrder) }}" method="POST">
                    @csrf
                    <div class="px-4 py-5 sm:p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordered</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Already Received</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">This Delivery</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-sm text-gray-500">SKU: {{ $item->product->sku }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->quantity_ordered }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->quantity_received }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="number" 
                                               name="items[{{ $item->id }}][quantity_received]"
                                               value="0"
                                               min="0" 
                                               max="{{ $item->quantity_ordered - $item->quantity_received }}"
                                               class="w-20 px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->quantity_ordered - $item->quantity_received }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-6">
                            <button type="submit" 
                                    class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 flex items-center">
                                <i class="fas fa-check mr-2"></i>
                                Confirm Receipt
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection