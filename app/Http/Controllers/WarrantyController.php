<?php
// app/Http/Controllers/WarrantyController.php

namespace App\Http\Controllers;

use App\Models\Warranty;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WarrantyController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_warranties')->only(['index', 'show']);
        $this->middleware('permission:create_warranties')->only(['create', 'store']);
        $this->middleware('permission:edit_warranties')->only(['edit', 'update']);
        $this->middleware('permission:delete_warranties')->only(['destroy']);
    }

    public function index()
    {
        $warranties = Warranty::with(['customer', 'product', 'sale'])
            ->filter(request(['status', 'type', 'customer', 'product']))
            ->orderBy('end_date')
            ->paginate(20);

        $stats = [
            'total' => Warranty::count(),
            'active' => Warranty::active()->count(),
            'expired' => Warranty::expired()->count(),
            'expiring_soon' => Warranty::expiringSoon(30)->count(),
        ];

        return view('warranties.index', compact('warranties', 'stats'));
    }

    public function create()
    {
        $sales = Sale::with(['customer', 'items.product'])
            ->where('created_at', '>=', Carbon::now()->subYear())
            ->whereHas('customer')
            ->whereHas('items')
            ->latest()
            ->get();

        $warrantyTypes = [
            'manufacturer' => 'Manufacturer Warranty',
            'store' => 'Store Warranty',
            'extended' => 'Extended Warranty'
        ];

        return view('warranties.create', compact('sales', 'warrantyTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'sale_item_id' => 'required|exists:sale_items,id',
            'type' => 'required|in:manufacturer,store,extended',
            'duration_months' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'serial_number' => 'nullable|string|max:255',
            'batch_number' => 'nullable|string|max:255',
        ]);

        $saleItem = SaleItem::with(['sale.customer', 'product'])->findOrFail($request->sale_item_id);

        // Check if warranty already exists for this sale item
        $existingWarranty = Warranty::where('sale_item_id', $request->sale_item_id)->first();
        if ($existingWarranty) {
            return back()->with('error', 'A warranty already exists for this sale item.');
        }

        // Check if the sale item can have warranty (not fully returned)
        if ($saleItem->quantity_remaining <= 0) {
            return back()->with('error', 'Cannot create warranty for fully returned item.');
        }

        $warranty = Warranty::create([
            'sale_id' => $request->sale_id,
            'sale_item_id' => $request->sale_item_id,
            'customer_id' => $saleItem->sale->customer_id,
            'product_id' => $saleItem->product_id,
            'type' => $request->type,
            'duration_months' => $request->duration_months,
            'start_date' => $request->start_date,
            'end_date' => Carbon::parse($request->start_date)->addMonths($request->duration_months),
            'terms' => $request->terms,
            'notes' => $request->notes,
            'serial_number' => $request->serial_number,
            'batch_number' => $request->batch_number,
            'status' => 'active',
        ]);

        return redirect()->route('warranties.show', $warranty)
            ->with('success', 'Warranty created successfully!');
    }

    public function show(Warranty $warranty)
    {
        $warranty->load(['sale', 'saleItem', 'customer', 'product', 'claims' => function($query) {
            $query->with('resolvedBy')->latest();
        }]);

        return view('warranties.show', compact('warranty'));
    }

    public function edit(Warranty $warranty)
    {
        $warrantyTypes = [
            'manufacturer' => 'Manufacturer Warranty',
            'store' => 'Store Warranty',
            'extended' => 'Extended Warranty'
        ];

        return view('warranties.edit', compact('warranty', 'warrantyTypes'));
    }

    public function update(Request $request, Warranty $warranty)
    {
        $request->validate([
            'type' => 'required|in:manufacturer,store,extended',
            'duration_months' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,expired,void',
        ]);

        $warranty->update([
            'type' => $request->type,
            'duration_months' => $request->duration_months,
            'start_date' => $request->start_date,
            'end_date' => Carbon::parse($request->start_date)->addMonths($request->duration_months),
            'terms' => $request->terms,
            'notes' => $request->notes,
            'status' => $request->status,
        ]);

        return redirect()->route('warranties.show', $warranty)
            ->with('success', 'Warranty updated successfully!');
    }

    public function destroy(Warranty $warranty)
    {
        if ($warranty->claims()->exists()) {
            return redirect()->route('warranties.index')
                ->with('error', 'Cannot delete warranty with existing claims.');
        }

        $warranty->delete();

        return redirect()->route('warranties.index')
            ->with('success', 'Warranty deleted successfully!');
    }

    public function getSaleItems(Sale $sale)
    {
        $sale->load(['items.product', 'customer']);
        
        $items = $sale->items->map(function($item) {
            return [
                'id' => $item->id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'quantity_remaining' => $item->quantity_remaining,
                'unit_price' => $item->unit_price,
                'can_create_warranty' => $item->quantity_remaining > 0 && 
                                    !Warranty::where('sale_item_id', $item->id)->exists()
            ];
        });
        
        return response()->json([
            'customer' => $sale->customer,
            'items' => $items
        ]);
    }
}