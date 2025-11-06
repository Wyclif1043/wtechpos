<?php
// app/Http/Controllers/ReturnController.php

namespace App\Http\Controllers;

use App\Models\SaleReturn;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_returns')->only(['index', 'show']);
        $this->middleware('permission:create_returns')->only(['create', 'store']);
        $this->middleware('permission:edit_returns')->only(['edit', 'update']);
        $this->middleware('permission:process_returns')->only(['process', 'refund']);
    }

    public function index()
    {
        $returns = SaleReturn::with(['sale', 'customer', 'user'])
            ->filter(request(['status', 'customer', 'date_from', 'date_to']))
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => SaleReturn::count(),
            'pending' => SaleReturn::pending()->count(),
            'approved' => SaleReturn::approved()->count(),
            'completed' => SaleReturn::completed()->count(),
            'total_refunded' => SaleReturn::refunded()->sum('refund_amount'),
        ];

        return view('returns.index', compact('returns', 'stats'));
    }

    public function create()
    {
        $customers = Customer::has('sales')->with('sales')->get();
        $reasons = [
            'defective' => 'Defective Product',
            'wrong_item' => 'Wrong Item Received',
            'changed_mind' => 'Changed Mind',
            'duplicate' => 'Duplicate Order',
            'other' => 'Other'
        ];

        $conditions = [
            'new' => 'New (Unopened)',
            'opened' => 'Opened',
            'damaged' => 'Damaged',
            'defective' => 'Defective'
        ];

        return view('returns.create', compact('customers', 'reasons', 'conditions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.condition' => 'required|in:new,opened,damaged,defective',
            'reason' => 'required|in:defective,wrong_item,changed_mind,duplicate,other',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $sale = Sale::with(['customer', 'items.product'])->findOrFail($request->sale_id);

            // Validate quantities
            foreach ($request->items as $itemData) {
                $saleItem = $sale->items()->findOrFail($itemData['sale_item_id']);
                
                if ($itemData['quantity'] > $saleItem->quantity_remaining) {
                    throw new \Exception("Cannot return more than purchased for {$saleItem->product->name}. Maximum: {$saleItem->quantity_remaining}");
                }
            }

            // Create return
            $return = SaleReturn::create([
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'user_id' => auth()->id(),
                'reason' => $request->reason,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            $totalAmount = 0;
            $refundAmount = 0;

            // Create return items
            foreach ($request->items as $itemData) {
                $saleItem = $sale->items()->with('product')->findOrFail($itemData['sale_item_id']);
                
                $itemTotal = $saleItem->unit_price * $itemData['quantity'];
                $itemRefund = $this->calculateRefundAmount($itemTotal, $itemData['condition']);
                
                $return->items()->create([
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'quantity_returned' => $itemData['quantity'],
                    'unit_price' => $saleItem->unit_price,
                    'total_amount' => $itemTotal,
                    'refund_amount' => $itemRefund,
                    'condition' => $itemData['condition'],
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalAmount += $itemTotal;
                $refundAmount += $itemRefund;
            }

            // Update return totals
            $return->update([
                'total_amount' => $totalAmount,
                'refund_amount' => $refundAmount,
            ]);

            DB::commit();

            return redirect()->route('returns.show', $return)
                ->with('success', 'Return request created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating return: ' . $e->getMessage());
        }
    }

    public function show(SaleReturn $return)
    {
        $return->load(['sale', 'customer', 'user', 'items.saleItem.product']);
        
        return view('returns.show', compact('return'));
    }

    public function process(Request $request, SaleReturn $return)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        $return->update([
            'status' => $request->status,
            'notes' => $return->notes . "\n\nAdmin: " . $request->admin_notes,
            'processed_at' => now(),
        ]);

        $status = $request->status === 'approved' ? 'approved' : 'rejected';

        return redirect()->route('returns.show', $return)
            ->with('success', "Return {$status} successfully!");
    }

    public function refund(Request $request, SaleReturn $return)
    {
        if (!$return->can_refund) {
            return back()->with('error', 'Return cannot be refunded at this time.');
        }

        $request->validate([
            'refund_method' => 'required|in:original,cash,card,credit_note',
            'refund_reference' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $return->update([
                'refund_method' => $request->refund_method,
                'refund_reference' => $request->refund_reference,
                'refunded_at' => now(),
                'status' => 'completed',
            ]);

            // If refunding to credit, update customer credit balance
            if ($request->refund_method === 'credit_note') {
                $return->customer->increment('credit_balance', $return->refund_amount);
                
                // Record credit transaction
                \App\Models\CustomerCreditTransaction::create([
                    'customer_id' => $return->customer_id,
                    'type' => 'refund',
                    'amount' => $return->refund_amount,
                    'previous_balance' => $return->customer->credit_balance - $return->refund_amount,
                    'new_balance' => $return->customer->credit_balance,
                    'reference_type' => SaleReturn::class,
                    'reference_id' => $return->id,
                    'notes' => 'Refund for return ' . $return->return_number,
                    'user_id' => auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()->route('returns.show', $return)
                ->with('success', 'Refund processed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing refund: ' . $e->getMessage());
        }
    }

    public function getCustomerSales(Customer $customer)
    {
        $sales = $customer->sales()
            ->with(['items' => function($query) {
                $query->whereRaw('quantity > COALESCE((SELECT SUM(quantity_returned) FROM return_items WHERE return_items.sale_item_id = sale_items.id), 0)');
            }, 'items.product'])
            ->where('created_at', '>=', now()->subDays(30)) // Last 30 days
            ->latest()
            ->get();

        return response()->json($sales);
    }

    public function getSaleItems(Sale $sale)
    {
        $sale->load(['items' => function($query) {
            $query->with(['product', 'returnItems'])
                  ->whereRaw('quantity > COALESCE((SELECT SUM(quantity_returned) FROM return_items WHERE return_items.sale_item_id = sale_items.id), 0)');
        }]);

        return response()->json($sale->items);
    }

    private function calculateRefundAmount($amount, $condition)
    {
        // Adjust refund amount based on item condition
        return match($condition) {
            'new' => $amount, // Full refund
            'opened' => $amount * 0.8, // 80% refund for opened items
            'damaged' => $amount * 0.5, // 50% refund for damaged items
            'defective' => $amount, // Full refund for defective items
            default => $amount,
        };
    }
}