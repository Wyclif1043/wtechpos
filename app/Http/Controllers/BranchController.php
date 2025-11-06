<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:branches.view')->only(['index', 'show']);
        $this->middleware('permission:branches.create')->only(['create', 'store']);
        $this->middleware('permission:branches.edit')->only(['edit', 'update']);
        $this->middleware('permission:branches.delete')->only(['destroy']);
        $this->middleware('permission:branches.manage')->only(['inventoryReport']);
    }

    public function index()
    {
        $branches = Branch::withCount('products')->get();
        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'manager_name' => 'nullable|string|max:255'
        ]);

        Branch::create($validated);

        return redirect()->route('branches.index')->with('success', 'Branch created successfully!');
    }

    public function show(Branch $branch)
    {
        $branch->load(['products' => function($query) {
            $query->with('services');
        }]);
        
        $lowStockProducts = $branch->products()->lowStock()->get();
        $outOfStockProducts = $branch->products()->where('quantity', 0)->get();

        return view('branches.show', compact('branch', 'lowStockProducts', 'outOfStockProducts'));
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'manager_name' => 'nullable|string|max:255'
        ]);

        $branch->update($validated);

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully!');
    }

    public function destroy(Branch $branch)
    {
        // Check if branch has products
        if ($branch->products()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete branch that has products. Transfer products first.');
        }

        $branch->update(['is_active' => false]);
        return redirect()->route('branches.index')->with('success', 'Branch deactivated successfully!');
    }

    public function inventoryReport(Branch $branch)
    {
        $products = $branch->products()->with('services')->get();
        
        $report = [
            'total_products' => $products->count(),
            'low_stock_products' => $products->where('stock_status', 'low-stock')->count(),
            'out_of_stock_products' => $products->where('stock_status', 'out-of-stock')->count(),
            'total_value' => $products->sum(function($product) {
                return $product->quantity * $product->price;
            })
        ];

        return view('branches.inventory-report', compact('branch', 'products', 'report'));
    }

    public function branchAssignment()
{
    $users = User::with('branch')->get();
    $branches = Branch::active()->get();
    
    return view('users.branch-assignment', compact('users', 'branches'));
}

public function assignBranch(User $user, Request $request)
{
    $request->validate([
        'branch_id' => 'nullable|exists:branches,id'
    ]);

    $user->update(['branch_id' => $request->branch_id]);

    return redirect()->back()->with('success', 'Branch assigned successfully.');
}
}