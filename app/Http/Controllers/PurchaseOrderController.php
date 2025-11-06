<?php
// app/Http/Controllers/PurchaseOrderController.php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use App\Services\PdfService;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'user', 'items']);

        // Apply status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Apply supplier filter
        if ($request->has('supplier') && $request->supplier) {
            $query->where('supplier_id', $request->supplier);
        }

        // Apply date filters
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        $purchaseOrders = $query->latest()->paginate(20);
        $suppliers = Supplier::where('is_active', true)->get();

        return view('purchase-orders.index', compact('purchaseOrders', 'suppliers'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)
            ->with('category')
            ->get()
            ->groupBy('category.name');

        $suppliers = Supplier::where('is_active', true)->get();

        return view('purchase-orders.create', compact('products', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $totalCost = $item['quantity_ordered'] * $item['unit_cost'];
                $totalAmount += $totalCost;

                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $totalCost,
                ]);
            }

            $purchaseOrder->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating purchase order: ' . $e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'user', 'items.product']);
        
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only pending purchase orders can be edited.');
        }

        $products = Product::where('is_active', true)
            ->with('category')
            ->get()
            ->groupBy('category.name');

        $suppliers = Supplier::where('is_active', true)->get();
        $purchaseOrder->load('items.product');

        return view('purchase-orders.edit', compact('purchaseOrder', 'products', 'suppliers'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return back()->with('error', 'Only pending purchase orders can be updated.');
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Update purchase order
            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'notes' => $request->notes,
            ]);

            // Remove existing items
            $purchaseOrder->items()->delete();

            // Add new items
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalCost = $item['quantity_ordered'] * $item['unit_cost'];
                $totalAmount += $totalCost;

                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $totalCost,
                ]);
            }

            $purchaseOrder->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating purchase order: ' . $e->getMessage());
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return back()->with('error', 'Only pending purchase orders can be deleted.');
        }

        try {
            DB::beginTransaction();
            
            $purchaseOrder->items()->delete();
            $purchaseOrder->delete();
            
            DB::commit();

            return redirect()->route('purchase-orders.index')
                ->with('success', 'Purchase order deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting purchase order: ' . $e->getMessage());
        }
    }

    public function receive(PurchaseOrder $purchaseOrder, Request $request)
    {
        if (!in_array($purchaseOrder->status, ['pending', 'partially_received'])) {
            return back()->with('error', 'This purchase order cannot be received.');
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.quantity_received' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $allReceived = true;

            foreach ($request->items as $itemId => $itemData) {
                $poItem = $purchaseOrder->items()->findOrFail($itemId);
                $quantityReceived = $itemData['quantity_received'];

                if ($quantityReceived > 0) {
                    $poItem->update(['quantity_received' => $quantityReceived]);

                    // Update product stock
                    $product = $poItem->product;
                    $previousStock = $product->stock_quantity;
                    $product->increment('stock_quantity', $quantityReceived);

                    // Record stock movement
                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'purchase',
                        'quantity' => $quantityReceived,
                        'previous_stock' => $previousStock,
                        'new_stock' => $previousStock + $quantityReceived,
                        'unit_cost' => $poItem->unit_cost,
                        'reference_type' => PurchaseOrder::class,
                        'reference_id' => $purchaseOrder->id,
                        'reason' => 'Purchase order receipt',
                        'user_id' => auth()->id(),
                    ]);

                    // Update product purchase price if this is the latest purchase
                    $product->update(['purchase_price' => $poItem->unit_cost]);
                }

                if ($quantityReceived < $poItem->quantity_ordered) {
                    $allReceived = false;
                }
            }

            // Update purchase order status
            $purchaseOrder->update([
                'status' => $allReceived ? 'received' : 'partially_received',
                'received_date' => now(),
            ]);

            DB::commit();

            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase order items received successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error receiving items: ' . $e->getMessage());
        }
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return back()->with('error', 'Only pending purchase orders can be cancelled.');
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order cancelled successfully!');
    }

    public function __construct()
{
    $this->middleware('permission:view_products')->only(['index', 'show']);
    $this->middleware('permission:create_products')->only(['create', 'store']);
    $this->middleware('permission:edit_products')->only(['edit', 'update']);
    $this->middleware('permission:delete_products')->only(['destroy']);
    
    // Add PDF middleware
    $this->middleware('permission:view_reports')->only([
        'purchaseOrderPdf', 'deliveryNotePdf', 'grnPdf', 
        'supplierInvoicePdf', 'paymentVoucherPdf', 'paymentReceiptPdf'
    ]);
}

public function purchaseOrderPdf(PurchaseOrder $purchaseOrder)
{
    $pdfService = new PdfService();
    return $pdfService->generatePurchaseOrder($purchaseOrder);
}

public function deliveryNotePdf(PurchaseOrder $purchaseOrder)
{
    $pdfService = new PdfService();
    return $pdfService->generateDeliveryNote($purchaseOrder);
}

public function grnPdf(PurchaseOrder $purchaseOrder)
{
    $pdfService = new PdfService();
    return $pdfService->generateGoodsReceivedNote($purchaseOrder);
}

public function supplierInvoicePdf(PurchaseOrder $purchaseOrder)
{
    $pdfService = new PdfService();
    return $pdfService->generateSupplierInvoice($purchaseOrder);
}

public function paymentVoucherPdf(PurchaseOrder $purchaseOrder, Request $request)
{
    $request->validate([
        'payment_date' => 'required|date',
        'payment_method' => 'required|string',
        'amount_paid' => 'required|numeric|min:0',
        'reference_number' => 'nullable|string',
    ]);

    $paymentData = $request->only(['payment_date', 'payment_method', 'amount_paid', 'reference_number']);
    
    $pdfService = new PdfService();
    return $pdfService->generatePaymentVoucher($purchaseOrder, $paymentData);
}

public function paymentReceiptPdf(PurchaseOrder $purchaseOrder, Request $request)
{
    $request->validate([
        'payment_date' => 'required|date',
        'payment_method' => 'required|string',
        'amount_paid' => 'required|numeric|min:0',
        'reference_number' => 'nullable|string',
    ]);

    $paymentData = $request->only(['payment_date', 'payment_method', 'amount_paid', 'reference_number']);
    
    $pdfService = new PdfService();
    return $pdfService->generatePaymentReceipt($purchaseOrder, $paymentData);
}
}