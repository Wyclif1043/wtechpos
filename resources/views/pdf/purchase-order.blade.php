<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .company-name { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .document-title { font-size: 18px; margin: 10px 0; }
        .section { margin: 15px 0; }
        .section-title { font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
        .row { display: flex; margin-bottom: 5px; }
        .col-6 { width: 50%; }
        .table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; }
        .signature-line { margin-top: 50px; border-top: 1px solid #333; width: 200px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company['name'] }}</div>
        <div>{{ $company['address'] }}</div>
        <div>Tel: {{ $company['phone'] }} | Email: {{ $company['email'] }}</div>
        <div class="document-title">PURCHASE ORDER</div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="section">
                <div class="section-title">Supplier Information</div>
                <div><strong>{{ $purchaseOrder->supplier->name }}</strong></div>
                <div>{{ $purchaseOrder->supplier->address }}</div>
                <div>Contact: {{ $purchaseOrder->supplier->contact_person }}</div>
                <div>Phone: {{ $purchaseOrder->supplier->phone }}</div>
                <div>Email: {{ $purchaseOrder->supplier->email }}</div>
            </div>
        </div>
        <div class="col-6">
            <div class="section">
                <div class="section-title">Order Information</div>
                <div><strong>PO Number:</strong> {{ $purchaseOrder->po_number }}</div>
                <div><strong>Order Date:</strong> {{ $purchaseOrder->order_date->format('M d, Y') }}</div>
                <div><strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('M d, Y') : 'Not specified' }}</div>
                <div><strong>Status:</strong> {{ strtoupper($purchaseOrder->status) }}</div>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>SKU</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->product->sku }}</td>
                <td class="text-right">{{ number_format($item->quantity_ordered) }}</td>
                <td class="text-right">${{ number_format($item->unit_cost, 2) }}</td>
                <td class="text-right">${{ number_format($item->total_cost, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>Total Amount:</strong></td>
                <td class="text-right"><strong>${{ number_format($purchaseOrder->total_amount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    @if($purchaseOrder->notes)
    <div class="section">
        <div class="section-title">Notes</div>
        <div>{{ $purchaseOrder->notes }}</div>
    </div>
    @endif

    <div class="footer">
        <div class="row">
            <div class="col-6">
                <div><strong>Prepared By:</strong></div>
                <div>{{ $purchaseOrder->user->name }}</div>
                <div class="signature-line"></div>
            </div>
            <div class="col-6">
                <div><strong>Authorized By:</strong></div>
                <div class="signature-line"></div>
            </div>
        </div>
    </div>
</body>
</html>