<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $sale->sale_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            @page {
                margin: 0;
                size: 80mm auto;
            }
            body {
                margin: 0;
                padding: 10px;
                font-size: 12px;
            }
            .no-print {
                display: none !important;
            }
        }
        body {
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body class="bg-white text-black max-w-md mx-auto">
    <!-- Print Button -->
    <div class="no-print text-center mb-4">
        <button onclick="window.print()" class="bg-blue-500 text-white px-4 py-2 rounded">
            <i class="fas fa-print mr-2"></i>Print Receipt
        </button>
        <button onclick="window.close()" class="bg-gray-500 text-white px-4 py-2 rounded ml-2">
            <i class="fas fa-times mr-2"></i>Close
        </button>
    </div>

    <!-- Receipt Content -->
    <div class="text-center border-b border-gray-300 pb-2 mb-2">
        <h1 class="text-xl font-bold">{{ config('app.name', 'POS System') }}</h1>
        <p class="text-sm">Daily Sales Receipt</p>
    </div>

    <!-- Sale Info -->
    <div class="mb-3">
        <div class="flex justify-between text-sm">
            <span>Receipt #:</span>
            <span class="font-bold">{{ $sale->sale_number }}</span>
        </div>
        <div class="flex justify-between text-sm">
            <span>Date:</span>
            <span>{{ $sale->created_at->format('M j, Y') }}</span>
        </div>
        <div class="flex justify-between text-sm">
            <span>Time:</span>
            <span>{{ $sale->created_at->format('g:i A') }}</span>
        </div>
        <div class="flex justify-between text-sm">
            <span>Customer:</span>
            <span>{{ $sale->customer->name ?? 'Walk-in Customer' }}</span>
        </div>
    </div>

    <!-- Items -->
    <div class="border-t border-gray-300 pt-2 mb-3">
        <div class="text-center font-bold mb-1">ITEMS</div>
        @foreach($sale->items as $item)
        <div class="flex justify-between text-sm mb-1">
            <div class="flex-1">
                <div>{{ $item->product->name }}</div>
                <div class="text-xs text-gray-600">
                    {{ $item->quantity }} x ${{ number_format($item->unit_price, 2) }}
                </div>
            </div>
            <div class="font-bold">${{ number_format($item->total_price, 2) }}</div>
        </div>
        @endforeach
    </div>

    <!-- Totals -->
    <div class="border-t border-gray-300 pt-2">
        <div class="flex justify-between text-sm">
            <span>Subtotal:</span>
            <span>${{ number_format($sale->subtotal, 2) }}</span>
        </div>
        <div class="flex justify-between text-sm">
            <span>Tax (16%):</span>
            <span>${{ number_format($sale->tax_amount, 2) }}</span>
        </div>
        @if($sale->discount_amount > 0)
        <div class="flex justify-between text-sm text-red-600">
            <span>Discount:</span>
            <span>-${{ number_format($sale->discount_amount, 2) }}</span>
        </div>
        @endif
        <div class="flex justify-between font-bold border-t border-gray-300 mt-1 pt-1">
            <span>TOTAL:</span>
            <span>${{ number_format($sale->total_amount, 2) }}</span>
        </div>
    </div>

    <!-- Payments -->
    <div class="border-t border-gray-300 pt-2 mt-3">
        <div class="text-center font-bold mb-1">PAYMENTS</div>
        @foreach($sale->payments as $payment)
        <div class="flex justify-between text-sm">
            <span class="capitalize">{{ $payment->payment_method }}:</span>
            <span>${{ number_format($payment->amount, 2) }}</span>
        </div>
        @endforeach
        @if($sale->balance_due > 0)
        <div class="flex justify-between text-sm text-red-600">
            <span>Balance Due:</span>
            <span>${{ number_format($sale->balance_due, 2) }}</span>
        </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="border-t border-gray-300 pt-2 mt-3 text-center text-xs">
        <p>Thank you for your business!</p>
        <p>{{ config('app.name', 'POS System') }}</p>
        <p>{{ $sale->created_at->format('m/d/Y H:i:s') }}</p>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>