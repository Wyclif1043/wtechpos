<?php
// app/Http/Controllers/SaleController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleDiscount;
use App\Models\HeldSale;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function posInterface()
    {
        $products = Product::where('is_active', true)
            ->with('category')
            ->get()
            ->groupBy('category.name');
            
        $customers = Customer::all();
        
        return view('pos.interface', compact('products', 'customers'));
    }

    // Add these methods to your existing SaleController

    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'items.product', 'payments'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('status') && $request->status) {
            $query->where('payment_status', $request->status);
        }

        $sales = $query->paginate(15);

        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.product', 'payments']);
        return view('sales.show', compact('sale'));
    }

    public function dailyReport(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        
        $sales = Sale::with(['customer', 'items', 'payments'])
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'asc')
            ->get();
    
        $summary = [
            'total_sales' => $sales->sum('total_amount'),
            'transaction_count' => $sales->count(),
            'average_sale' => $sales->count() > 0 ? $sales->sum('total_amount') / $sales->count() : 0,
            'total_tax' => $sales->sum('tax_amount'),
            'payment_methods' => $this->getPaymentMethodSummary($sales)
        ];
    
        // Use the simpler view path
        return view('sales.daily', [
            'sales' => $sales,
            'summary' => $summary,
            'report_date' => $date
        ]);
    }

    private function getPaymentMethodSummary($sales)
    {
        $summary = [];
        
        foreach ($sales as $sale) {
            foreach ($sale->payments as $payment) {
                $method = $payment->payment_method;
                if (!isset($summary[$method])) {
                    $summary[$method] = 0;
                }
                $summary[$method] += $payment->amount;
            }
        }
        
        return $summary;
    }
    public function receipt(Sale $sale)
    {
        $sale->load(['customer', 'items.product', 'payments']);
        return view('sales.receipt', compact('sale'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->track_stock && $product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Only ' . $product->stock_quantity . ' available.'
            ]);
        }

        $cart = session()->get('pos.cart', []);
        $key = $request->product_id;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $request->quantity;
        } else {
            $cart[$key] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->selling_price,
                'quantity' => $request->quantity,
                'stock_quantity' => $product->stock_quantity
            ];
        }

        session()->put('pos.cart', $cart);

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'cart_total' => $this->calculateCartTotal($cart)
        ]);
    }

    public function applyDiscount(Request $request)
    {
        $request->validate([
            'discount_type' => 'required|in:percentage,fixed_amount,manual',
            'discount_value' => 'required|numeric|min:0',
            'discount_name' => 'nullable|string|max:255',
        ]);

        $cart = session()->get('pos.cart', []);
        
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ]);
        }

        $subtotal = $this->calculateCartTotal($cart)['subtotal'];
        $discountAmount = 0;

        switch ($request->discount_type) {
            case 'percentage':
                $discountAmount = ($subtotal * $request->discount_value) / 100;
                break;
                
            case 'fixed_amount':
                $discountAmount = min($request->discount_value, $subtotal);
                break;
                
            case 'manual':
                $discountAmount = min($request->discount_value, $subtotal);
                break;
        }

        // Store discount in session
        session()->put('pos.discount', [
            'type' => $request->discount_type,
            'value' => $request->discount_value,
            'amount' => $discountAmount,
            'name' => $request->discount_name ?: 'Manual Discount',
        ]);

        return response()->json([
            'success' => true,
            'discount_amount' => $discountAmount,
            'cart_total' => $this->calculateCartTotal($cart)
        ]);
    }

    public function removeDiscount()
    {
        session()->forget('pos.discount');

        $cart = session()->get('pos.cart', []);
        
        return response()->json([
            'success' => true,
            'cart_total' => $this->calculateCartTotal($cart)
        ]);
    }

    public function getApplicableDiscounts(Request $request)
    {
        $customerId = $request->get('customer_id');
        $cart = session()->get('pos.cart', []);
        
        $cartItems = array_map(function($item) {
            return [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ];
        }, $cart);

        $subtotal = $this->calculateCartTotal($cart)['subtotal'];

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

    private function calculateCartTotal($cart)
    {
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $taxRate = 0.16; // 16% VAT
        $taxAmount = $subtotal * $taxRate;
        
        // Apply discount if exists
        $discountAmount = 0;
        $sessionDiscount = session()->get('pos.discount');
        if ($sessionDiscount) {
            $discountAmount = $sessionDiscount['amount'];
        }
        
        $totalAmount = $subtotal + $taxAmount - $discountAmount;

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => max(0, $totalAmount), // Ensure total doesn't go negative
        ];
    }
    
    public function completeSale(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|in:cash,card,mobile_money,credit,check,gift_card',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.reference' => 'nullable|string|max:255',
            'payments.*.notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ]);
    
        $cart = session()->get('pos.cart', []);
    
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ]);
        }
    
        try {
            DB::beginTransaction();
    
            // Calculate totals
            $totals = $this->calculateCartTotal($cart);
            $subtotal = $totals['subtotal'];
            $taxAmount = $totals['tax_amount'];
            $discountAmount = $totals['discount_amount'];
            $totalAmount = $totals['total_amount'];
    
            // Validate payments total
            $totalPaid = array_sum(array_column($request->payments, 'amount'));
            
            if ($totalPaid < $totalAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total payments (' . number_format($totalPaid, 2) . ') is less than total amount (' . number_format($totalAmount, 2) . ')'
                ]);
            }
    
            // Create sale
            $sale = Sale::create([
                'sale_number' => 'SALE-' . date('Ymd') . '-' . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT),
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'total_paid' => $totalPaid,
                'balance_due' => max(0, $totalAmount - $totalPaid),
                'payment_status' => $totalPaid >= $totalAmount ? 'paid' : 'partial',
                'notes' => $request->notes,
            ]);
    
            // Create sale items and update stock
            foreach ($cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                ]);
    
                // Update product stock
                $product = Product::find($item['product_id']);
                if ($product && $product->track_stock) {
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }
    
            // Record payments
            foreach ($request->payments as $paymentData) {
                $sale->payments()->create([
                    'payment_method' => $paymentData['method'],
                    'amount' => $paymentData['amount'],
                    'reference' => $paymentData['reference'] ?? null,
                    'notes' => $paymentData['notes'] ?? null,
                    'user_id' => auth()->id(),
                ]);
            }
    
            // Record discount if applied
            if ($discountData = session()->get('pos.discount')) {
                SaleDiscount::create([
                    'sale_id' => $sale->id,
                    'discount_name' => $discountData['name'],
                    'discount_type' => $discountData['type'],
                    'discount_value' => $discountData['value'],
                    'discount_amount' => $discountData['amount'],
                    'applied_to' => 'sale',
                ]);
            }
    
            // Update customer loyalty points if customer exists and not credit sale
            if ($request->customer_id) {
                $customer = Customer::find($request->customer_id);
                $customer->increment('total_spent', $totalAmount);
                
                // Only add loyalty points for non-credit payments
                $nonCreditPayments = array_filter($request->payments, function($payment) {
                    return $payment['method'] !== 'credit';
                });
                
                if (count($nonCreditPayments) > 0) {
                    $points = $totalAmount * 0.1; // 10% of total as points
                    if (method_exists($customer, 'addLoyaltyPoints')) {
                        $customer->addLoyaltyPoints($points, $sale, 'Purchase points');
                    }
                }
                
                $customer->update(['last_purchase' => now()]);
            }
    
            DB::commit();
    
            // Clear cart and discount
            session()->forget(['pos.cart', 'pos.discount']);
    
            $changeAmount = max(0, $totalPaid - $totalAmount);
    
            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'total_amount' => $totalAmount,
                'total_paid' => $totalPaid,
                'change_amount' => $changeAmount,
                'message' => 'Sale completed successfully!'
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error completing sale: ' . $e->getMessage()
            ]);
        }
    }
    
    public function addPaymentToSale(Request $request, Sale $sale)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card,mobile_money,credit,check,gift_card',
            'amount' => 'required|numeric|min:0.01|max:' . $sale->balance_due,
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);
    
        try {
            DB::beginTransaction();
    
            $payment = $sale->addPayment(
                $request->payment_method,
                $request->amount,
                $request->reference,
                $request->notes
            );
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'payment' => $payment,
                'balance_due' => $sale->balance_due,
                'payment_status' => $sale->payment_status,
                'message' => 'Payment added successfully!'
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error adding payment: ' . $e->getMessage()
            ]);
        }
    }

    public function getCart()
    {
        $cart = session()->get('pos.cart', []);
        return response()->json([
            'cart' => $cart,
            'cart_total' => $this->calculateCartTotal($cart)
        ]);
    }

    public function updateCart(Request $request)
    {
        $cart = session()->get('pos.cart', []);
        $productId = $request->product_id;

        if ($request->action === 'remove') {
            unset($cart[$productId]);
        } elseif ($request->action === 'update') {
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = $request->quantity;
                
                // Check stock
                if ($cart[$productId]['quantity'] > $cart[$productId]['stock_quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock'
                    ]);
                }
            }
        }

        session()->put('pos.cart', $cart);

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'cart_total' => $this->calculateCartTotal($cart)
        ]);
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('search');
        
        $products = Product::where('is_active', true)
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->with('category')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function holdSale(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'notes' => 'nullable|string',
        ]);

        $cart = session()->get('pos.cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot hold empty cart'
            ]);
        }

        // Calculate totals
        $totals = $this->calculateCartTotal($cart);

        try {
            $heldSale = HeldSale::create([
                'user_id' => auth()->id(),
                'customer_id' => $request->customer_id,
                'cart_data' => $cart,
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'discount_amount' => $totals['discount_amount'],
                'total_amount' => $totals['total_amount'],
                'notes' => $request->notes,
            ]);

            // Clear current cart
            session()->forget('pos.cart');

            return response()->json([
                'success' => true,
                'hold_number' => $heldSale->hold_number,
                'message' => 'Sale held successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error holding sale: ' . $e->getMessage()
            ]);
        }
    }

    public function getHeldSales()
    {
        $heldSales = HeldSale::active()
            ->with(['customer', 'user'])
            ->orderBy('held_at', 'desc')
            ->get();

        return response()->json($heldSales);
    }

    public function recallSale(HeldSale $heldSale)
    {
        // Check if sale is still valid
        if ($heldSale->status !== 'held' || $heldSale->is_expired) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot recall expired or completed hold'
            ]);
        }

        // Check if user has permission to recall this sale
        if ($heldSale->user_id !== auth()->id() && !auth()->user()->hasPermission('manage_held_sales')) {
            return response()->json([
                'success' => false,
                'message' => 'You can only recall your own held sales'
            ]);
        }

        // Restore cart from held sale
        session()->put('pos.cart', $heldSale->cart_data);

        // Update held sale status
        $heldSale->update(['status' => 'completed']);

        return response()->json([
            'success' => true,
            'message' => 'Sale recalled successfully!',
            'cart' => $heldSale->cart_data,
            'cart_total' => $this->calculateCartTotal($heldSale->cart_data)
        ]);
    }

    public function cancelHeldSale(HeldSale $heldSale)
    {
        $heldSale->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Held sale cancelled successfully!'
        ]);
    }
}