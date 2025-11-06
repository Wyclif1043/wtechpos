<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $receiptNumber }}</title>
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
        .receipt-box { border: 2px solid #333; padding: 20px; margin: 20px 0; }
        .amount-large { font-size: 18px; font-weight: bold; text-align: center; margin: 10px 0; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; }
        .signature-line { margin-top: 50px; border-top: 1px solid #333; width: 200px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company['name'] }}</div>
        <div>{{ $company['address'] }}</div>
        <div>Tel: {{ $company['phone'] }} | Email: {{ $company['email'] }}</div>
        <div class="document-title">PAYMENT RECEIPT</div>
    </div>

    <div class="receipt-box">
        <div class="text-center">
            <div><strong>RECEIPT NO:</strong> {{ $receiptNumber }}</div>
            <div><strong>DATE:</strong> {{ \Carbon\Carbon::parse($paymentData['payment_date'])->format('M d, Y') }}</div>
        </div>
        
        <div class="amount-large">
            ${{ number_format($paymentData['amount_paid'], 2) }}
        </div>

        <div class="section">
            <div class="section-title">Received From</div>
            <div><strong>{{ $company['name'] }}</strong></div>
            <div>{{ $company['address'] }}</div>
        </div>

        <div class="section">
            <div class="section-title">Payment Details</div>
            <div><strong>Amount:</strong> ${{ number_format($paymentData['amount_paid'], 2) }}</div>
            <div><strong>Payment Method:</strong> {{ $paymentData['payment_method'] }}</div>
            <div><strong>Reference No:</strong> {{ $paymentData['reference_number'] ?? 'N/A' }}</div>
            <div><strong>For:</strong> Payment for Purchase Order {{ $purchaseOrder->po_number }}</div>
        </div>

        <div class="section">
            <div class="section-title">Received By</div>
            <div><strong>{{ $purchaseOrder->supplier->name }}</strong></div>
            <div>{{ $purchaseOrder->supplier->address }}</div>
            <div>Contact: {{ $purchaseOrder->supplier->contact_person }}</div>
        </div>
    </div>

    <div class="footer">
        <div class="row">
            <div class="col-6">
                <div><strong>Payer's Signature:</strong></div>
                <div class="signature-line"></div>
                <div>Name: ___________________</div>
                <div>Date: ___________________</div>
            </div>
            <div class="col-6">
                <div><strong>Receiver's Signature:</strong></div>
                <div class="signature-line"></div>
                <div>Name: ___________________</div>
                <div>Date: ___________________</div>
            </div>
        </div>
    </div>

    <div class="text-center" style="margin-top: 20px; font-style: italic;">
        This is a computer-generated receipt. No signature required.
    </div>
</body>
</html>