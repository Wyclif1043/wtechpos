<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductWarranty;
use Illuminate\Http\Request;

class ProductWarrantyController extends Controller
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
        $warranties = ProductWarranty::with('product')
            ->filter(request()->all())
            ->latest()
            ->paginate(20);

        return view('product-warranties.index', compact('warranties'));
    }

    public function create()
    {
        $products = Product::active()->get();
        $warrantyTypes = [
            'manufacturer' => 'Manufacturer Warranty',
            'store' => 'Store Warranty', 
            'extended' => 'Extended Warranty'
        ];

        return view('product-warranties.create', compact('products', 'warrantyTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warranty_name' => 'required|string|max:255',
            'type' => 'required|in:manufacturer,store,extended',
            'duration_months' => 'required|integer|min:1',
            'terms' => 'nullable|string',
            'coverage_details' => 'nullable|string',
        ]);

        ProductWarranty::create([
            'product_id' => $request->product_id,
            'warranty_name' => $request->warranty_name,
            'type' => $request->type,
            'duration_months' => $request->duration_months,
            'terms' => $request->terms,
            'coverage_details' => $request->coverage_details,
            'is_active' => true,
        ]);

        return redirect()->route('product-warranties.index')
            ->with('success', 'Product warranty created successfully!');
    }

    public function show(ProductWarranty $productWarranty)
    {
        $productWarranty->load('product');
        return view('product-warranties.show', compact('productWarranty'));
    }

    public function edit(ProductWarranty $productWarranty)
    {
        $products = Product::active()->get();
        $warrantyTypes = [
            'manufacturer' => 'Manufacturer Warranty',
            'store' => 'Store Warranty',
            'extended' => 'Extended Warranty'
        ];

        return view('product-warranties.edit', compact('productWarranty', 'products', 'warrantyTypes'));
    }

    public function update(Request $request, ProductWarranty $productWarranty)
    {
        $request->validate([
            'warranty_name' => 'required|string|max:255',
            'type' => 'required|in:manufacturer,store,extended',
            'duration_months' => 'required|integer|min:1',
            'terms' => 'nullable|string',
            'coverage_details' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $productWarranty->update($request->all());

        return redirect()->route('product-warranties.show', $productWarranty)
            ->with('success', 'Product warranty updated successfully!');
    }

    public function destroy(ProductWarranty $productWarranty)
    {
        // Check if this warranty is used in any sales
        if ($productWarranty->saleWarranties()->exists()) {
            return redirect()->route('product-warranties.index')
                ->with('error', 'Cannot delete warranty that is used in sales.');
        }

        $productWarranty->delete();

        return redirect()->route('product-warranties.index')
            ->with('success', 'Product warranty deleted successfully!');
    }

    public function getProductWarranties(Product $product)
    {
        $warranties = $product->activeWarranties()->get();
        
        return response()->json($warranties);
    }
}