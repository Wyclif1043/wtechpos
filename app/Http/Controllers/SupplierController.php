<?php
// app/Http/Controllers/SupplierController.php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_suppliers')->only(['index', 'show']);
        $this->middleware('permission:create_suppliers')->only(['create', 'store']);
        $this->middleware('permission:edit_suppliers')->only(['edit', 'update']);
        $this->middleware('permission:delete_suppliers')->only(['destroy']);
    }

    public function index()
    {
        $suppliers = Supplier::withCount('products')
            ->filter(request(['search', 'status']))
            ->orderBy('name')
            ->paginate(20);

        $supplierStats = [
            'total' => Supplier::count(),
            'active' => Supplier::where('is_active', true)->count(),
            'inactive' => Supplier::where('is_active', false)->count(),
        ];

        return view('suppliers.index', compact('suppliers', 'supplierStats'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        Supplier::create([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => true,
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully!');
    }

    public function show(Supplier $supplier)
    {
        $supplier->loadCount('products');
        
        $products = $supplier->products()
            ->with(['category'])
            ->orderBy('name')
            ->paginate(10);

        $recentPurchaseOrders = PurchaseOrder::where('supplier_id', $supplier->id)
            ->with(['user'])
            ->latest()
            ->limit(10)
            ->get();

        $purchaseStats = PurchaseOrder::where('supplier_id', $supplier->id)
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total_amount) as total_spent,
                AVG(total_amount) as average_order,
                MIN(created_at) as first_order,
                MAX(created_at) as last_order
            ')
            ->first();

        return view('suppliers.show', compact(
            'supplier', 
            'products', 
            'recentPurchaseOrders', 
            'purchaseStats'
        ));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier->update([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Supplier updated successfully!');
    }

    public function destroy(Supplier $supplier)
    {
        // Check if supplier has products
        if ($supplier->products()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Cannot delete supplier with associated products. You can deactivate the supplier instead.');
        }

        // Check if supplier has purchase orders
        if (PurchaseOrder::where('supplier_id', $supplier->id)->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Cannot delete supplier with purchase order history. You can deactivate the supplier instead.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully!');
    }

    public function toggleStatus(Supplier $supplier)
    {
        $supplier->update([
            'is_active' => !$supplier->is_active,
        ]);

        $status = $supplier->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Supplier {$status} successfully!");
    }

    public function products(Supplier $supplier)
    {
        $products = $supplier->products()
            ->with(['category'])
            ->filter(request(['search', 'stock_status']))
            ->orderBy('name')
            ->paginate(20);

        return view('suppliers.products', compact('supplier', 'products'));
    }

    public function purchaseOrders(Supplier $supplier)
    {
        $purchaseOrders = PurchaseOrder::where('supplier_id', $supplier->id)
            ->with(['user', 'items.product'])
            ->filter(request(['status', 'date_from', 'date_to']))
            ->latest()
            ->paginate(20);

        return view('suppliers.purchase-orders', compact('supplier', 'purchaseOrders'));
    }
}