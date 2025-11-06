<?php

namespace App\Http\Controllers;

use App\Models\WarrantyClaim;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ProductWarranty;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WarrantyClaimController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_warranty_claims')->only(['index', 'show']);
        $this->middleware('permission:create_warranty_claims')->only(['create', 'store']);
        $this->middleware('permission:edit_warranty_claims')->only(['edit', 'update']);
        $this->middleware('permission:delete_warranty_claims')->only(['destroy']);
    }

    public function index()
    {
        $claims = WarrantyClaim::with(['sale', 'customer', 'product', 'productWarranty', 'resolvedBy'])
            ->filter(request()->all())
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => WarrantyClaim::count(),
            'pending' => WarrantyClaim::pending()->count(),
            'resolved' => WarrantyClaim::resolved()->count(),
            'under_warranty' => WarrantyClaim::underWarranty()->count(),
        ];

        return view('warranty-claims.index', compact('claims', 'stats'));
    }

    public function create()
    {
        // Get recent sales with products that have warranties
        $sales = Sale::with(['customer', 'items.product.productWarranties' => function($query) {
                $query->active();
            }])
            ->where('created_at', '>=', Carbon::now()->subMonths(24)) // Last 2 years
            ->whereHas('items.product.productWarranties', function($query) {
                $query->active();
            })
            ->latest()
            ->get()
            ->filter(function($sale) {
                return $sale->customer; // Only include sales with customers
            });

        $issueTypes = [
            'repair' => 'Repair',
            'replacement' => 'Replacement', 
            'refund' => 'Refund'
        ];

        return view('warranty-claims.create', compact('sales', 'issueTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'sale_item_id' => 'required|exists:sale_items,id',
            'product_warranty_id' => 'required|exists:product_warranties,id',
            'claim_date' => 'required|date',
            'issue_type' => 'required|in:repair,replacement,refund',
            'problem_description' => 'required|string|min:10',
        ]);

        $saleItem = SaleItem::with(['sale.customer', 'product'])->findOrFail($request->sale_item_id);
        $productWarranty = ProductWarranty::findOrFail($request->product_warranty_id);

        // Check if item is under warranty
        $warrantyEndDate = $saleItem->sale->created_at->copy()->addMonths($productWarranty->duration_months);
        $isUnderWarranty = now()->lte($warrantyEndDate);

        if (!$isUnderWarranty) {
            return back()->with('error', 'This product is no longer under warranty. Warranty ended on ' . $warrantyEndDate->format('M d, Y'));
        }

        // Check if item hasn't been fully returned
        if ($saleItem->quantity_remaining <= 0) {
            return back()->with('error', 'Cannot create claim for returned item.');
        }

        $claim = WarrantyClaim::create([
            'sale_id' => $request->sale_id,
            'sale_item_id' => $request->sale_item_id,
            'customer_id' => $saleItem->sale->customer_id,
            'product_id' => $saleItem->product_id,
            'product_warranty_id' => $request->product_warranty_id,
            'claim_date' => $request->claim_date,
            'issue_type' => $request->issue_type,
            'problem_description' => $request->problem_description,
            'status' => 'submitted',
        ]);

        return redirect()->route('warranty-claims.show', $claim)
            ->with('success', 'Warranty claim submitted successfully!');
    }

    public function show(WarrantyClaim $warrantyClaim)
    {
        $warrantyClaim->load(['sale', 'saleItem', 'customer', 'product', 'productWarranty', 'resolvedBy']);
        
        return view('warranty-claims.show', compact('warrantyClaim'));
    }

    public function edit(WarrantyClaim $warrantyClaim)
    {
        $warrantyClaim->load(['sale', 'saleItem', 'customer', 'product', 'productWarranty']);
        $issueTypes = [
            'repair' => 'Repair',
            'replacement' => 'Replacement',
            'refund' => 'Refund'
        ];

        $statuses = [
            'submitted' => 'Submitted',
            'in_progress' => 'In Progress',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'completed' => 'Completed'
        ];

        return view('warranty-claims.edit', compact('warrantyClaim', 'issueTypes', 'statuses'));
    }

    public function update(Request $request, WarrantyClaim $warrantyClaim)
    {
        $request->validate([
            'issue_type' => 'required|in:repair,replacement,refund',
            'problem_description' => 'required|string|min:10',
            'resolution_notes' => 'nullable|string',
            'repair_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:submitted,in_progress,approved,rejected,completed',
            'resolution_date' => 'nullable|date|required_if:status,approved,rejected,completed',
            'customer_feedback' => 'nullable|string',
        ]);

        $updateData = $request->only([
            'issue_type', 'problem_description', 'resolution_notes',
            'repair_cost', 'status', 'customer_feedback'
        ]);

        // Set resolution date and resolved by if status is resolved
        if (in_array($request->status, ['approved', 'rejected', 'completed'])) {
            $updateData['resolution_date'] = $request->resolution_date ?? now();
            $updateData['resolved_by'] = auth()->id();
        } else {
            $updateData['resolution_date'] = null;
            $updateData['resolved_by'] = null;
        }

        $warrantyClaim->update($updateData);

        return redirect()->route('warranty-claims.show', $warrantyClaim)
            ->with('success', 'Warranty claim updated successfully!');
    }

    public function destroy(WarrantyClaim $warrantyClaim)
    {
        $warrantyClaim->delete();

        return redirect()->route('warranty-claims.index')
            ->with('success', 'Warranty claim deleted successfully!');
    }

    public function updateStatus(Request $request, WarrantyClaim $warrantyClaim)
    {
        $request->validate([
            'status' => 'required|in:submitted,in_progress,approved,rejected,completed',
            'resolution_notes' => 'nullable|string',
        ]);

        $updateData = [
            'status' => $request->status,
            'resolution_notes' => $request->resolution_notes,
        ];

        if (in_array($request->status, ['approved', 'rejected', 'completed'])) {
            $updateData['resolution_date'] = now();
            $updateData['resolved_by'] = auth()->id();
        }

        $warrantyClaim->update($updateData);

        return back()->with('success', 'Claim status updated successfully!');
    }

    // AJAX method to get sale items with warranties
    public function getSaleItems(Sale $sale)
    {
        $sale->load(['items.product.productWarranties' => function($query) {
            $query->active();
        }, 'customer']);

        $items = $sale->items->map(function($item) {
            $warranties = $item->product->productWarranties->map(function($warranty) use ($item) {
                $warrantyEndDate = $item->sale->created_at->copy()->addMonths($warranty->duration_months);
                $isUnderWarranty = now()->lte($warrantyEndDate);
                
                return [
                    'id' => $warranty->id,
                    'name' => $warranty->warranty_name,
                    'type' => $warranty->type,
                    'duration' => $warranty->formatted_duration,
                    'end_date' => $warrantyEndDate->format('M d, Y'),
                    'is_under_warranty' => $isUnderWarranty,
                    'remaining_days' => $isUnderWarranty ? now()->diffInDays($warrantyEndDate, false) : 0,
                ];
            });

            return [
                'id' => $item->id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'quantity_remaining' => $item->quantity_remaining,
                'unit_price' => $item->unit_price,
                'warranties' => $warranties,
                'can_claim' => $item->quantity_remaining > 0 && $warranties->where('is_under_warranty', true)->isNotEmpty(),
            ];
        });

        return response()->json([
            'customer' => $sale->customer,
            'sale_date' => $sale->created_at->format('M d, Y'),
            'items' => $items
        ]);
    }
}