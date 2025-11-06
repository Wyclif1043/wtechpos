<?php
// app/Http/Controllers/CustomerController.php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('sales');

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Apply credit balance filter
        if ($request->has('has_credit')) {
            if ($request->has_credit == '1') {
                $query->where('credit_balance', '>', 0);
            } elseif ($request->has_credit == '0') {
                $query->where('credit_balance', '<=', 0);
            }
        }

        // Apply loyalty points filter
        if ($request->has('has_points')) {
            if ($request->has_points == '1') {
                $query->where('loyalty_points', '>', 0);
            } elseif ($request->has_points == '0') {
                $query->where('loyalty_points', '<=', 0);
            }
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:20|unique:customers,phone',
            'address' => 'nullable|string',
        ]);

        Customer::create($request->all());

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully!');
    }

    public function show(Customer $customer)
    {
        $customer->load(['sales' => function($query) {
            $query->with('items.product')->latest()->limit(10);
        }, 'creditTransactions' => function($query) {
            $query->with('user')->latest()->limit(10);
        }, 'loyaltyTransactions' => function($query) {
            $query->latest()->limit(10);
        }]);

        $stats = [
            'total_sales' => $customer->sales()->count(),
            'total_spent' => $customer->total_spent,
            'average_sale' => $customer->sales()->avg('total_amount') ?? 0,
            'last_purchase' => $customer->last_purchase?->diffForHumans() ?? 'Never',
        ];

        return view('customers.show', compact('customer', 'stats'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20|unique:customers,phone,' . $customer->id,
            'address' => 'nullable|string',
        ]);

        $customer->update($request->all());

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully!');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer with sales history.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully!');
    }

    public function salesHistory(Customer $customer)
    {
        $sales = $customer->sales()
            ->with(['items.product', 'user'])
            ->latest()
            ->paginate(20);

        return view('customers.sales-history', compact('customer', 'sales'));
    }
}