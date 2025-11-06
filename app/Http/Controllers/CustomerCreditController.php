<?php
// app/Http/Controllers/CustomerCreditController.php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerCreditTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerCreditController extends Controller
{
    public function index()
    {
        $customers = Customer::withCredit()
            ->orderBy('credit_balance', 'desc')
            ->paginate(20);

        $totalCredit = Customer::sum('credit_balance');

        return view('customers.credit.index', compact('customers', 'totalCredit'));
    }

    public function show(Customer $customer)
    {
        $transactions = $customer->creditTransactions()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('customers.credit.show', compact('customer', 'transactions'));
    }

    public function createPayment(Customer $customer)
    {
        return view('customers.credit.payment', compact('customer'));
    }

    public function processPayment(Request $request, Customer $customer)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $customer->credit_balance,
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $customer->addPayment($request->amount, $request->notes);

            DB::commit();

            return redirect()->route('customers.credit.show', $customer)
                ->with('success', 'Payment processed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    public function adjustCredit(Request $request, Customer $customer)
    {
        $request->validate([
            'type' => 'required|in:add,deduct',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $amount = $request->type === 'add' ? $request->amount : -$request->amount;
            $previousBalance = $customer->credit_balance;
            
            if ($request->type === 'deduct' && $request->amount > $customer->credit_balance) {
                throw new \Exception('Deduction amount exceeds current credit balance');
            }

            $customer->increment('credit_balance', $amount);

            CustomerCreditTransaction::create([
                'customer_id' => $customer->id,
                'type' => 'adjustment',
                'amount' => $amount,
                'previous_balance' => $previousBalance,
                'new_balance' => $customer->credit_balance,
                'notes' => $request->reason . ': ' . $request->notes,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('customers.credit.show', $customer)
                ->with('success', 'Credit adjustment processed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error adjusting credit: ' . $e->getMessage());
        }
    }
}