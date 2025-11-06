<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_users')->only(['index', 'show']);
        $this->middleware('permission:create_users')->only(['create', 'store']);
        $this->middleware('permission:edit_users')->only(['edit', 'update']);
        $this->middleware('permission:delete_users')->only(['destroy']);
        
    }

    public function index()
    {
        $users = User::withCount('sales')
            ->orderBy('name')
            ->paginate(20);

        $userStats = [
            'total' => User::count(),
            'active' => User::active()->count(),
            'admins' => User::byRole('admin')->count(),
            'managers' => User::byRole('manager')->count(),
            'cashiers' => User::byRole('cashier')->count(),
            'accountants' => User::byRole('accountant')->count(),
        ];

        return view('users.index', compact('users', 'userStats'));
    }

    public function create()
    {
        $roles = [
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'cashier' => 'Cashier',
            'accountant' => 'Accountant',
        ];

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,manager,cashier,accountant',
            'pin_code' => 'required|digits:4|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'pin_code' => $request->pin_code,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        $user->loadCount('sales');
        
        $recentSales = $user->sales()
            ->with('customer')
            ->latest()
            ->limit(10)
            ->get();

        $salesStats = $user->sales()
            ->selectRaw('
                COUNT(*) as total_sales,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as average_sale,
                MIN(created_at) as first_sale,
                MAX(created_at) as last_sale
            ')
            ->first();

        return view('users.show', compact('user', 'recentSales', 'salesStats'));
    }


    public function branchAssignment()
{
    \Log::info('Branch assignment method called');
    \Log::info('User:', auth()->user() ? ['id' => auth()->id(), 'name' => auth()->user()->name] : 'Not logged in');
    
    $users = User::with('branch')
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    $branches = Branch::where('is_active', true)
        ->orderBy('name')
        ->get();

    \Log::info('Users count: ' . $users->count());
    \Log::info('Branches count: ' . $branches->count());

    return view('users.branch-assignment', compact('users', 'branches'));
}

    /**
     * Assign branch to user
     */
    public function assignBranch(Request $request, User $user)
    {
        $request->validate([
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        // Check if user is trying to modify their own branch assignment
        if (auth()->id() === $user->id && $request->branch_id != $user->branch_id) {
            return back()->with('error', 'You cannot change your own branch assignment.');
        }

        $user->update([
            'branch_id' => $request->branch_id ?: null
        ]);

        // Log the activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'branch_id' => $request->branch_id,
                'branch_name' => $request->branch_id ? Branch::find($request->branch_id)->name : 'None'
            ])
            ->log('assigned branch');

        return back()->with('success', "Branch assigned successfully for {$user->name}!");
    }

    public function edit(User $user)
    {
        $roles = [
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'cashier' => 'Cashier',
            'accountant' => 'Accountant',
        ];

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,manager,cashier,accountant',
            'pin_code' => 'required|digits:4|unique:users,pin_code,' . $user->id,
            'is_active' => 'boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'pin_code' => $request->pin_code,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->sales()->exists()) {
            return redirect()->route('users.index')
                ->with('error', 'Cannot delete user with sales history. You can deactivate the user instead.');
        }

        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully!');
    }

    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    public function toggleStatus(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User {$status} successfully!");
    }
}