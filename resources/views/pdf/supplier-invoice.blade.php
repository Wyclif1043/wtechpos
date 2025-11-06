<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoiceNumber }}</title>
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
        .terms { margin-top: 20px; font-style: italic; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $purchaseOrder->supplier->name }}</div>
        <div>{{ $purchaseOrder->supplier->address }}</div>
        <div>Tel: {{ $purchaseOrder->supplier->phone }} | Email: {{ $purchaseOrder->supplier->email }}</div>
        <div class="document-title">INVOICE</div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="section">
                <div class="section-title">Bill To</div>
                <div><strong>{{ $company['name'] }}</strong></div>
                <div>{{ $company['address'] }}</div>
                <div>Tel: {{ $company['phone'] }}</div>
            </div>
        </div>
        <div class="col-6">
            <div class="section">
                <div class="section-title">Invoice Details</div>
                <div><strong>Invoice No:</strong> {{ $invoiceNumber }}</div>
                <div><strong>Invoice Date:</strong> {{ $invoiceDate->format('M d, Y') }}</div>
                <div><strong>Due Date:</strong> {{ $dueDate->format('M d, Y') }}</div>
                <div><strong>PO Reference:</strong> {{ $purchaseOrder->po_number }}</div>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Description</th>
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
                <td>{{ $item->product->description ?: 'N/A' }}</td>
                <td class="text-right">{{ number_format($item->quantity_ordered) }}</td>
                <td class="text-right">${{ number_format($item->unit_cost, 2) }}</td>
                <td class="text-right">${{ number_format($item->total_cost, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>Subtotal:</strong></td>
                <td class="text-right">${{ number_format($purchaseOrder->total_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>Tax (0%):</strong></td>
                <td class="text-right">$0.00</td>
            </tr>
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>Total Amount:</strong></td>
                <td class="text-right"><strong>${{ number_format($purchaseOrder->total_amount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="terms">
        <div><strong>Payment Terms:</strong> Net 30 days</div>
        <div><strong>Payment Method:</strong> Bank Transfer</div>
        <div><strong>Bank Details:</strong> [Supplier Bank Details Here]</div>
    </div>

    <div class="footer">
        <div class="text-center">
            <div>Thank you for your business!</div>
            <div>For any inquiries, please contact: {{ $purchaseOrder->supplier->contact_person }} at {{ $purchaseOrder->supplier->phone }}</div>
        </div>
    </div>
</body>
</html>