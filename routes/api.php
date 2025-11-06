<?php
// routes/api.php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Barcode search
    Route::get('/products/barcode/{barcode}', function ($barcode) {
        $product = \App\Models\Product::where('barcode', $barcode)
            ->where('is_active', true)
            ->first();
            
        if ($product) {
            return response()->json($product);
        }
        
        return response()->json(null, 404);
    });
    
    // Quick search
    Route::get('/products/search', function (Request $request) {
        $search = $request->get('q');
        $limit = $request->get('limit', 10);
        
        $products = \App\Models\Product::where('is_active', true)
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->with('category')
            ->limit($limit)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'selling_price' => $product->selling_price,
                    'stock_quantity' => $product->stock_quantity,
                    'min_stock' => $product->min_stock,
                    'category' => $product->category->name,
                ];
            });
            
        return response()->json($products);
    });
});