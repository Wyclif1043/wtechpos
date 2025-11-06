<?php
// app/Http/Controllers/StockAdjustmentController.php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\Product;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $query = StockAdjustment::with(['product', 'user']);

        // Apply type filter
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Apply reason filter
        if ($request->has('reason') && $request->reason) {
            $query->where('reason', $request->reason);
        }

        // Apply date filters
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $adjustments = $query->latest()->paginate(50);

        return view('stock-adjustments.index', compact('adjustments'));

    }

    public function create()
    {
        $products = Product::where('is_active', true)
            ->where('track_stock', true)
            ->with('category')
            ->get();

            return view('stock-adjustments.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:add,remove',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost_value' => 'nullable|numeric|min:0',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($request->type === 'remove' && $product->stock_quantity < $request->quantity) {
            return back()->with('error', 'Insufficient stock for adjustment. Current stock: ' . $product->stock_quantity);
        }

        $adjustment = StockAdjustment::create([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'type' => $request->type,
            'reason' => $request->reason,
            'description' => $request->description,
            'previous_stock' => $product->stock_quantity,
            'new_stock' => $request->type === 'add' 
                ? $product->stock_quantity + $request->quantity
                : $product->stock_quantity - $request->quantity,
            'cost_value' => $request->cost_value,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('stock-adjustments.index')
            ->with('success', 'Stock adjustment created successfully!');
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load(['product', 'user']);
         return view('stock-adjustments.show', compact('stockAdjustment'));
    }
}