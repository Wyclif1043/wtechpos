<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Note - {{ $deliveryNoteNumber }}</title>
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
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; }
        .signature-line { margin-top: 50px; border-top: 1px solid #333; width: 200px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company['name'] }}</div>
        <div class="document-title">DELIVERY NOTE</div>
        <div><strong>Delivery Note No:</strong> {{ $deliveryNoteNumber }}</div>
        <div><strong>PO Reference:</strong> {{ $purchaseOrder->po_number }}</div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="section">
                <div class="section-title">From (Supplier)</div>
                <div><strong>{{ $purchaseOrder->supplier->name }}</strong></div>
                <div>{{ $purchaseOrder->supplier->address }}</div>
                <div>Contact: {{ $purchaseOrder->supplier->contact_person }}</div>
            </div>
        </div>
        <div class="col-6">
            <div class="section">
                <div class="section-title">To (Receiver)</div>
                <div><strong>{{ $company['name'] }}</strong></div>
                <div>{{ $company['address'] }}</div>
                <div>Tel: {{ $company['phone'] }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Delivery Information</div>
        <div class="row">
            <div class="col-6"><strong>Delivery Date:</strong> {{ now()->format('M d, Y') }}</div>
            <div class="col-6"><strong>PO Date:</strong> {{ $purchaseOrder->order_date->format('M d, Y') }}</div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>SKU</th>
                <th>Quantity Ordered</th>
                <th>Quantity Delivered</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->product->sku }}</td>
                <td class="text-right">{{ number_format($item->quantity_ordered) }}</td>
                <td class="text-right">{{ number_format($item->quantity_received) }}</td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="row">
            <div class="col-6">
                <div><strong>Supplier Signature:</strong></div>
                <div class="signature-line"></div>
                <div>Name: ___________________</div>
                <div>Date: ___________________</div>
            </div>
            <div class="col-6">
                <div><strong>Receiver Signature:</strong></div>
                <div class="signature-line"></div>
                <div>Name: ___________________</div>
                <div>Date: ___________________</div>
            </div>
        </div>
    </div>
</body>
</html>