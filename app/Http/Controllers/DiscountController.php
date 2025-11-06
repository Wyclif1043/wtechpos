<?php
// app/Http/Controllers/DiscountController.php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DiscountController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_discounts')->only(['index', 'show']);
        $this->middleware('permission:create_discounts')->only(['create', 'store']);
        $this->middleware('permission:edit_discounts')->only(['edit', 'update']);
        $this->middleware('permission:delete_discounts')->only(['destroy']);
    }

    public function index()
    {
        $discounts = Discount::withCount('saleDiscounts')
            ->filter(request(['status', 'type', 'scope']))
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Discount::count(),
            'active' => Discount::active()->count(),
            'expired' => Discount::where('end_date', '<', Carbon::now())->count(),
            'total_uses' => Discount::sum('used_count'),
        ];

        return view('discounts.index', compact('discounts', 'stats'));
    }

    public function create()
    {
        $types = [
            'percentage' => 'Percentage Discount',
            'fixed_amount' => 'Fixed Amount Discount',
            'buy_x_get_y' => 'Buy X Get Y Free'
        ];

        $scopes = [
            'sale' => 'Entire Sale',
            'product' => 'Specific Products',
            'category' => 'Product Categories',
            'customer' => 'Specific Customers'
        ];

        $products = Product::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        $customers = Customer::all();

        return view('discounts.create', compact('types', 'scopes', 'products', 'categories', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed_amount,buy_x_get_y',
            'value' => 'required|numeric|min:0',
            'scope' => 'required|in:sale,product,category,customer',
            'scope_ids' => 'nullable|array',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'max_uses' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'apply_automatically' => 'boolean',
        ]);

        $discount = Discount::create([
            'name' => $request->name,
            'type' => $request->type,
            'value' => $request->value,
            'scope' => $request->scope,
            'scope_ids' => $request->scope_ids,
            'min_purchase_amount' => $request->min_purchase_amount,
            'min_quantity' => $request->min_quantity,
            'max_uses' => $request->max_uses,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
            'apply_automatically' => $request->has('apply_automatically'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('discounts.show', $discount)
            ->with('success', 'Discount created successfully!');
    }

    public function show(Discount $discount)
    {
        $discount->load(['saleDiscounts' => function($query) {
            $query->with(['sale.customer', 'saleItem.product'])->latest()->limit(10);
        }]);

        $usageStats = [
            'total_uses' => $discount->used_count,
            'remaining_uses' => $discount->remaining_uses,
            'completion_rate' => $discount->max_uses ? ($discount->used_count / $discount->max_uses) * 100 : null,
        ];

        return view('discounts.show', compact('discount', 'usageStats'));
    }

    public function edit(Discount $discount)
    {
        $types = [
            'percentage' => 'Percentage Discount',
            'fixed_amount' => 'Fixed Amount Discount',
            'buy_x_get_y' => 'Buy X Get Y Free'
        ];

        $scopes = [
            'sale' => 'Entire Sale',
            'product' => 'Specific Products',
            'category' => 'Product Categories',
            'customer' => 'Specific Customers'
        ];

        $products = Product::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        $customers = Customer::all();

        return view('discounts.edit', compact('discount', 'types', 'scopes', 'products', 'categories', 'customers'));
    }

    public function update(Request $request, Discount $discount)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed_amount,buy_x_get_y',
            'value' => 'required|numeric|min:0',
            'scope' => 'required|in:sale,product,category,customer',
            'scope_ids' => 'nullable|array',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'max_uses' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'apply_automatically' => 'boolean',
        ]);

        $discount->update([
            'name' => $request->name,
            'type' => $request->type,
            'value' => $request->value,
            'scope' => $request->scope,
            'scope_ids' => $request->scope_ids,
            'min_purchase_amount' => $request->min_purchase_amount,
            'min_quantity' => $request->min_quantity,
            'max_uses' => $request->max_uses,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
            'apply_automatically' => $request->has('apply_automatically'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('discounts.show', $discount)
            ->with('success', 'Discount updated successfully!');
    }

    public function destroy(Discount $discount)
    {
        if ($discount->saleDiscounts()->exists()) {
            return redirect()->route('discounts.index')
                ->with('error', 'Cannot delete discount that has been used in sales.');
        }

        $discount->delete();

        return redirect()->route('discounts.index')
            ->with('success', 'Discount deleted successfully!');
    }

    public function toggleStatus(Discount $discount)
    {
        $discount->update([
            'is_active' => !$discount->is_active,
        ]);

        $status = $discount->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Discount {$status} successfully!");
    }

    public function getApplicableDiscounts(Request $request)
    {
        $customerId = $request->get('customer_id');
        $cartItems = $request->get('cart_items', []);
        $subtotal = $request->get('subtotal', 0);

        $discounts = Discount::active()
            ->automatic()
            ->get()
            ->filter(function($discount) use ($customerId, $cartItems, $subtotal) {
                return $this->isDiscountApplicable($discount, $customerId, $cartItems, $subtotal);
            })
            ->values();

        return response()->json($discounts);
    }

    private function isDiscountApplicable($discount, $customerId, $cartItems, $subtotal)
    {
        // Check scope-specific conditions
        switch ($discount->scope) {
            case 'sale':
                return $this->isSaleDiscountApplicable($discount, $subtotal);
                
            case 'product':
                return $this->isProductDiscountApplicable($discount, $cartItems);
                
            case 'category':
                return $this->isCategoryDiscountApplicable($discount, $cartItems);
                
            case 'customer':
                return $this->isCustomerDiscountApplicable($discount, $customerId);
                
            default:
                return false;
        }
    }

    private function isSaleDiscountApplicable($discount, $subtotal)
    {
        if ($discount->min_purchase_amount && $subtotal < $discount->min_purchase_amount) {
            return false;
        }
        
        return true;
    }

    private function isProductDiscountApplicable($discount, $cartItems)
    {
        $applicableProducts = $discount->scope_ids ?: [];
        
        foreach ($cartItems as $item) {
            if (in_array($item['product_id'], $applicableProducts)) {
                if (!$discount->min_quantity || $item['quantity'] >= $discount->min_quantity) {
                    return true;
                }
            }
        }
        
        return false;
    }

    private function isCategoryDiscountApplicable($discount, $cartItems)
    {
        // This would need product category data from the cart items
        // For now, return true if any item matches (simplified)
        return !empty($cartItems);
    }

    private function isCustomerDiscountApplicable($discount, $customerId)
    {
        if (!$customerId) {
            return false;
        }
        
        $applicableCustomers = $discount->scope_ids ?: [];
        return in_array($customerId, $applicableCustomers);
    }
}