<?php
// app/Services/PdfService.php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\View;

class PdfService
{
    public function generatePurchaseRequisition($requisitionData)
    {
        $pdf = Pdf::loadView('pdf.purchase-requisition', $requisitionData);
        return $pdf->stream('purchase-requisition-' . $requisitionData['requisition_number'] . '.pdf');
    }

    public function generatePurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $data = [
            'purchaseOrder' => $purchaseOrder->load(['supplier', 'items.product']),
            'company' => $this->getCompanyInfo(),
        ];

        $pdf = Pdf::loadView('pdf.purchase-order', $data);
        return $pdf->stream('purchase-order-' . $purchaseOrder->po_number . '.pdf');
    }

    public function generateDeliveryNote(PurchaseOrder $purchaseOrder)
    {
        $data = [
            'purchaseOrder' => $purchaseOrder->load(['supplier', 'items.product']),
            'company' => $this->getCompanyInfo(),
            'deliveryNoteNumber' => 'DN-' . date('Ymd') . '-' . $purchaseOrder->id,
        ];

        $pdf = Pdf::loadView('pdf.delivery-note', $data);
        return $pdf->stream('delivery-note-' . $purchaseOrder->po_number . '.pdf');
    }

    public function generateGoodsReceivedNote(PurchaseOrder $purchaseOrder)
    {
        $data = [
            'purchaseOrder' => $purchaseOrder->load(['supplier', 'items.product']),
            'company' => $this->getCompanyInfo(),
            'grnNumber' => 'GRN-' . date('Ymd') . '-' . $purchaseOrder->id,
        ];

        $pdf = Pdf::loadView('pdf.goods-received-note', $data);
        return $pdf->stream('grn-' . $purchaseOrder->po_number . '.pdf');
    }

    public function generateSupplierInvoice(PurchaseOrder $purchaseOrder)
    {
        $data = [
            'purchaseOrder' => $purchaseOrder->load(['supplier', 'items.product']),
            'company' => $this->getCompanyInfo(),
            'invoiceNumber' => 'INV-' . date('Ymd') . '-' . $purchaseOrder->id,
            'invoiceDate' => now(),
            'dueDate' => now()->addDays(30),
        ];

        $pdf = Pdf::loadView('pdf.supplier-invoice', $data);
        return $pdf->stream('invoice-' . $purchaseOrder->po_number . '.pdf');
    }

    public function generatePaymentVoucher(PurchaseOrder $purchaseOrder, $paymentData)
    {
        $data = [
            'purchaseOrder' => $purchaseOrder->load(['supplier', 'items.product']),
            'company' => $this->getCompanyInfo(),
            'voucherNumber' => 'PV-' . date('Ymd') . '-' . $purchaseOrder->id,
            'paymentData' => $paymentData,
        ];

        $pdf = Pdf::loadView('pdf.payment-voucher', $data);
        return $pdf->stream('payment-voucher-' . $purchaseOrder->po_number . '.pdf');
    }

    public function generatePaymentReceipt(PurchaseOrder $purchaseOrder, $paymentData)
    {
        $data = [
            'purchaseOrder' => $purchaseOrder->load(['supplier', 'items.product']),
            'company' => $this->getCompanyInfo(),
            'receiptNumber' => 'RCPT-' . date('Ymd') . '-' . $purchaseOrder->id,
            'paymentData' => $paymentData,
        ];

        $pdf = Pdf::loadView('pdf.payment-receipt', $data);
        return $pdf->stream('payment-receipt-' . $purchaseOrder->po_number . '.pdf');
    }

    private function getCompanyInfo()
    {
        return [
            'name' => config('app.company_name', 'Your Company Name'),
            'address' => config('app.company_address', '123 Business Street, City, State 12345'),
            'phone' => config('app.company_phone', '+1 (555) 123-4567'),
            'email' => config('app.company_email', 'info@company.com'),
            'logo' => public_path('images/company-logo.png'), // Make sure this file exists
        ];
    }
}