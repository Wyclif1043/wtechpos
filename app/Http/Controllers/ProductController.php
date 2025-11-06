<?php
// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_products')->only(['index', 'show']);
        $this->middleware('permission:create_products')->only(['create', 'store']);
        $this->middleware('permission:edit_products')->only(['edit', 'update']);
        $this->middleware('permission:delete_products')->only(['destroy']);
    }

    public function index()
    {
        $products = Product::with(['category', 'supplier', 'branch']) // Add branch relationship
            ->filter(request(['search', 'category', 'stock_status', 'branch'])) // Add branch to filter
            ->orderBy('name')
            ->paginate(20);

        $categories = Category::where('is_active', true)->get();
        $branches = Branch::active()->get(); // Add this line

        return view('products.index', compact('products', 'categories', 'branches')); // Add branches to view
    }

    public function create(Request $request)
    {
        $categories = Category::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $branches = Branch::active()->get();

        // Pre-fill barcode if provided in query string
        $prefilledBarcode = $request->get('barcode');
        
        return view('products.create', compact('categories', 'suppliers', 'branches', 'prefilledBarcode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'branch_id' => 'required|exists:branches,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'track_stock' => 'boolean',
        ]);

        $productData = $request->except('image', 'barcode', 'sku');

        // Generate unique barcode (EAN-13 format) - only if not manually provided
        if ($request->filled('barcode')) {
            // Validate manually entered barcode
            $request->validate([
                'barcode' => 'nullable|string|max:255|unique:products,barcode',
            ]);
            $productData['barcode'] = $request->barcode;
        } else {
            $productData['barcode'] = $this->generateUniqueBarcode();
        }
        
        // Generate SKU based on category and product name
        $productData['sku'] = $this->generateSKU($request->name, $request->category_id);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $productData['image'] = $imagePath;
        }

        $productData['is_active'] = $request->has('is_active');
        $productData['track_stock'] = $request->boolean('track_stock');

        Product::create($productData);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully!');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'supplier', 'branch', 'stockMovements' => function($query) {
            $query->with('user')->latest()->limit(10);
        }, 'saleItems' => function($query) {
            $query->with('sale')->latest()->limit(10);
        }]);

        $salesStats = $product->saleItems()
            ->selectRaw('
                SUM(quantity) as total_sold,
                SUM(total_price) as total_revenue,
                AVG(unit_price) as average_price
            ')
            ->first();

        return view('products.show', compact('product', 'salesStats'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $branches = Branch::active()->get();

        return view('products.edit', compact('product', 'categories', 'suppliers', 'branches'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'branch_id' => 'required|exists:branches,id', // Add branch validation
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'track_stock' => 'boolean',
        ]);

        $productData = $request->except('image');

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            
            $imagePath = $request->file('image')->store('products', 'public');
            $productData['image'] = $imagePath;
        }

        $productData['is_active'] = $request->has('is_active');
        $productData['track_stock'] = $request->has('track_stock');

        $product->update($productData);

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        // Check if product has sales history
        if ($product->saleItems()->exists()) {
            return redirect()->route('products.index')
                ->with('error', 'Cannot delete product with sales history. You can deactivate the product instead.');
        }

        // Delete product image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully!');
    }

    public function toggleStatus(Product $product)
    {
        $product->update([
            'is_active' => !$product->is_active,
        ]);

        $status = $product->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Product {$status} successfully!");
    }

    public function bulkUpdateStock(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.stock_quantity' => 'required|integer|min:0',
        ]);

        foreach ($request->products as $productData) {
            $product = Product::find($productData['id']);
            $product->update(['stock_quantity' => $productData['stock_quantity']]);
        }

        return back()->with('success', 'Stock quantities updated successfully!');
    }

    private function generateUniqueBarcode()
    {
        do {
            // Generate first 12 digits (EAN-13 without check digit)
            $barcode = '20' . mt_rand(1000000000, 9999999999); // 2-digit prefix + 10 random digits
            
            // Calculate EAN-13 check digit
            $barcode = $barcode . $this->calculateEAN13CheckDigit($barcode);
            
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Calculate EAN-13 check digit
     */
    private function calculateEAN13CheckDigit($barcode)
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int)$barcode[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }
        
        $checkDigit = (10 - ($sum % 10)) % 10;
        return $checkDigit;
    }

    /**
     * Generate SKU based on category and product name
     */
    private function generateSKU($productName, $categoryId)
    {
        $category = Category::find($categoryId);
        $categoryCode = strtoupper(substr($category->name, 0, 3));
        
        // Clean product name and get first 3 letters
        $productCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', substr($productName, 0, 3)));
        
        // Generate unique number
        $uniqueNumber = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        $sku = $categoryCode . '-' . $productCode . '-' . $uniqueNumber;
        
        // Ensure SKU is unique
        $counter = 1;
        $originalSku = $sku;
        while (Product::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }

    /**
     * Regenerate barcode for a product (optional feature)
     */
    public function regenerateBarcode(Product $product)
    {
        $product->update([
            'barcode' => $this->generateUniqueBarcode(),
        ]);

        return back()->with('success', 'Barcode regenerated successfully!');
    }

    /**
     * Regenerate SKU for a product (optional feature)
     */
    public function regenerateSKU(Product $product)
    {
        $product->update([
            'sku' => $this->generateSKU($product->name, $product->category_id),
        ]);

        return back()->with('success', 'SKU regenerated successfully!');
    }
}