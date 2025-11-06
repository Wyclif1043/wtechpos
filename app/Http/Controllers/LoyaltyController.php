<?php
// app/Http/Controllers/LoyaltyController.php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
    public function index()
    {
        $customers = Customer::withLoyaltyPoints()
            ->orderBy('loyalty_points', 'desc')
            ->paginate(20);

        $totalPoints = Customer::sum('loyalty_points');

        // Calculate tier counts
        $tierCounts = [
            'standard' => Customer::where('total_spent', '<', 100)->count(),
            'bronze' => Customer::whereBetween('total_spent', [100, 499.99])->count(),
            'silver' => Customer::whereBetween('total_spent', [500, 999.99])->count(),
            'gold' => Customer::where('total_spent', '>=', 1000)->count(),
        ];

        return view('customers.loyalty.index', compact('customers', 'totalPoints', 'tierCounts'));
    }

    public function show(Customer $customer)
    {
        $transactions = $customer->loyaltyTransactions()
            ->latest()
            ->paginate(20);

        return view('customers.loyalty.show', compact('customer', 'transactions'));
    }

    public function redeemPoints(Request $request, Customer $customer)
    {
        $request->validate([
            'points' => 'required|integer|min:1|max:' . $customer->loyalty_points,
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $customer->redeemLoyaltyPoints($request->points, $request->notes);

            DB::commit();

            return redirect()->route('customers.loyalty.show', $customer)
                ->with('success', 'Loyalty points redeemed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error redeeming points: ' . $e->getMessage());
        }
    }

    public function adjustPoints(Request $request, Customer $customer)
    {
        $request->validate([
            'type' => 'required|in:add,deduct',
            'points' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $points = $request->type === 'add' ? $request->points : -$request->points;
            
            if ($request->type === 'deduct' && $request->points > $customer->loyalty_points) {
                throw new \Exception('Deduction amount exceeds current loyalty points');
            }

            $previousPoints = $customer->loyalty_points;
            $customer->increment('loyalty_points', $points);

            LoyaltyTransaction::create([
                'customer_id' => $customer->id,
                'type' => 'adjustment',
                'points' => $points,
                'previous_points' => $previousPoints,
                'new_points' => $customer->loyalty_points,
                'notes' => $request->reason . ': ' . $request->notes,
            ]);

            DB::commit();

            return redirect()->route('customers.loyalty.show', $customer)
                ->with('success', 'Loyalty points adjusted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error adjusting points: ' . $e->getMessage());
        }
    }
}