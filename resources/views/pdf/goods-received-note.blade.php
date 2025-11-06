<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Goods Received Note - {{ $grnNumber }}</title>
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
        .condition { font-style: italic; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company['name'] }}</div>
        <div class="document-title">GOODS RECEIVED NOTE</div>
        <div><strong>GRN No:</strong> {{ $grnNumber }}</div>
        <div><strong>PO Reference:</strong> {{ $purchaseOrder->po_number }}</div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="section">
                <div class="section-title">Supplier Information</div>
                <div><strong>{{ $purchaseOrder->supplier->name }}</strong></div>
                <div>{{ $purchaseOrder->supplier->address }}</div>
                <div>Contact: {{ $purchaseOrder->supplier->contact_person }}</div>
            </div>
        </div>
        <div class="col-6">
            <div class="section">
                <div class="section-title">Receiving Information</div>
                <div><strong>Received Date:</strong> {{ now()->format('M d, Y') }}</div>
                <div><strong>PO Date:</strong> {{ $purchaseOrder->order_date->format('M d, Y') }}</div>
                <div><strong>Received By:</strong> {{ auth()->user()->name }}</div>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>SKU</th>
                <th>Quantity Ordered</th>
                <th>Quantity Received</th>
                <th>Unit Price</th>
                <th>Total Value</th>
                <th>Condition</th>
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
                <td class="text-right">${{ number_format($item->unit_cost, 2) }}</td>
                <td class="text-right">${{ number_format($item->quantity_received * $item->unit_cost, 2) }}</td>
                <td class="condition">Good</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section">
        <div class="section-title">Remarks</div>
        <div>All items received in good condition unless otherwise noted.</div>
    </div>

    <div class="footer">
        <div class="row">
            <div class="col-4">
                <div><strong>Received By:</strong></div>
                <div class="signature-line"></div>
                <div>Name: {{ auth()->user()->name }}</div>
                <div>Date: {{ now()->format('M d, Y') }}</div>
            </div>
            <div class="col-4">
                <div><strong>Checked By:</strong></div>
                <div class="signature-line"></div>
                <div>Name: ___________________</div>
                <div>Date: ___________________</div>
            </div>
            <div class="col-4">
                <div><strong>Authorized By:</strong></div>
                <div class="signature-line"></div>
                <div>Name: ___________________</div>
                <div>Date: ___________________</div>
            </div>
        </div>
    </div>
</body>
</html>