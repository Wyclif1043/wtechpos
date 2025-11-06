<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transfer Receipt - {{ $movement->reference_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .company-name { font-size: 24px; font-weight: bold; color: #333; }
        .document-title { font-size: 18px; color: #666; }
        .section { margin-bottom: 15px; }
        .section-title { font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .info-item { margin-bottom: 5px; }
        .label { font-weight: bold; color: #555; }
        .value { color: #333; }
        .signature-section { margin-top: 40px; }
        .signature-line { border-top: 1px solid #333; width: 200px; margin-top: 40px; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #666; }
        .status-badge { 
            padding: 3px 8px; 
            border-radius: 12px; 
            font-size: 10px; 
            font-weight: bold; 
            text-transform: uppercase; 
        }
        .pending { background: #fef3cd; color: #856404; }
        .approved { background: #cce7ff; color: #004085; }
        .shipped { background: #e2e3ff; color: #383d41; }
        .delivered { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">BEAUTY POS SYSTEM</div>
        <div class="document-title">PRODUCT TRANSFER RECEIPT</div>
    </div>

    <div class="section">
        <div class="section-title">Transfer Information</div>
        <div class="info-grid">
            <div>
                <div class="info-item">
                    <span class="label">Reference Number:</span>
                    <span class="value">{{ $movement->reference_number }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Product:</span>
                    <span class="value">{{ $movement->product->name }} ({{ $movement->product->sku }})</span>
                </div>
                <div class="info-item">
                    <span class="label">Quantity:</span>
                    <span class="value">{{ $movement->quantity }}</span>
                </div>
            </div>
            <div>
                <div class="info-item">
                    <span class="label">Status:</span>
                    <span class="status-badge {{ $movement->status }}">{{ strtoupper($movement->status) }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Transfer Date:</span>
                    <span class="value">{{ $movement->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Urgent:</span>
                    <span class="value">{{ $movement->is_urgent ? 'YES' : 'No' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Branch Information</div>
        <div class="info-grid">
            <div>
                <div class="info-item">
                    <span class="label">From Branch:</span>
                    <span class="value">{{ $movement->fromBranch->name }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Location:</span>
                    <span class="value">{{ $movement->fromBranch->location }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Phone:</span>
                    <span class="value">{{ $movement->fromBranch->phone ?? 'N/A' }}</span>
                </div>
            </div>
            <div>
                <div class="info-item">
                    <span class="label">To Branch:</span>
                    <span class="value">{{ $movement->toBranch->name }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Location:</span>
                    <span class="value">{{ $movement->toBranch->location }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Phone:</span>
                    <span class="value">{{ $movement->toBranch->phone ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Transfer Details</div>
        <div class="info-item">
            <span class="label">Reason:</span>
            <span class="value">{{ $movement->reason }}</span>
        </div>
        @if($movement->notes)
        <div class="info-item">
            <span class="label">Notes:</span>
            <span class="value">{{ $movement->notes }}</span>
        </div>
        @endif
        @if($movement->tracking_number)
        <div class="info-item">
            <span class="label">Tracking Number:</span>
            <span class="value">{{ $movement->tracking_number }}</span>
        </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Timeline & Signatures</div>
        <div class="info-grid">
            <div>
                <div class="info-item">
                    <span class="label">Requested By:</span>
                    <span class="value">{{ $movement->requestedBy->name }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Request Date:</span>
                    <span class="value">{{ $movement->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="signature-line"></div>
                <div style="text-align: center; font-size: 10px;">Requester Signature</div>
            </div>
            
            <div>
                @if($movement->approved_at)
                <div class="info-item">
                    <span class="label">Approved By:</span>
                    <span class="value">{{ $movement->approvedBy->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Approval Date:</span>
                    <span class="value">{{ $movement->approved_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="signature-line"></div>
                <div style="text-align: center; font-size: 10px;">Approver Signature</div>
                @endif
            </div>
        </div>

        <div class="info-grid" style="margin-top: 20px;">
            <div>
                @if($movement->shipped_at)
                <div class="info-item">
                    <span class="label">Shipped By:</span>
                    <span class="value">{{ $movement->processedBy->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Ship Date:</span>
                    <span class="value">{{ $movement->shipped_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="signature-line"></div>
                <div style="text-align: center; font-size: 10px;">Shipper Signature</div>
                @endif
            </div>
            
            <div>
                @if($movement->delivered_at)
                <div class="info-item">
                    <span class="label">Received By:</span>
                    <span class="value">{{ $movement->receivedBy->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Delivery Date:</span>
                    <span class="value">{{ $movement->delivered_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="signature-line"></div>
                <div style="text-align: center; font-size: 10px;">Receiver Signature</div>
                @endif
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is an computer-generated document. No physical signature is required.</p>
        <p>Generated on: {{ now()->format('M d, Y H:i:s') }}</p>
    </div>
</body>
</html>