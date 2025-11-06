<?php
// app/Http/Controllers/ShiftController.php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\CashDrop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_shifts')->only(['index', 'show', 'current']);
        $this->middleware('permission:start_shifts')->only(['create', 'store']);
        $this->middleware('permission:end_shifts')->only(['end', 'update']);
        $this->middleware('permission:manage_shifts')->only(['suspend', 'resume']);
    }

    public function index()
    {
        $shifts = Shift::with(['user'])
            ->filter(request(['status', 'user', 'date_from', 'date_to']))
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Shift::count(),
            'active' => Shift::active()->count(),
            'today' => Shift::today()->count(),
            'total_sales' => Shift::sum('total_sales'),
            'avg_shift_sales' => Shift::ended()->avg('total_sales'),
        ];

        return view('shifts.index', compact('shifts', 'stats'));
    }

    public function current()
    {
        $activeShifts = Shift::active()
            ->with(['user'])
            ->latest()
            ->get();

        return view('shifts.current', compact('activeShifts'));
    }

    public function create()
    {
        // Check if user already has an active shift
        if (auth()->user()->hasActiveShift()) {
            return redirect()->route('shifts.current')
                ->with('error', 'You already have an active shift. Please end your current shift before starting a new one.');
        }

        return view('shifts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'starting_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if user already has an active shift
        if (auth()->user()->hasActiveShift()) {
            return back()->with('error', 'You already have an active shift.');
        }

        $shift = Shift::create([
            'user_id' => auth()->id(),
            'starting_cash' => $request->starting_cash,
            'started_at' => now(),
            'status' => 'active',
            'notes' => $request->notes,
        ]);

        return redirect()->route('shifts.show', $shift)
            ->with('success', 'Shift started successfully!');
    }

    public function show(Shift $shift)
    {
        $shift->load(['user', 'cashDrops.user', 'sales' => function($query) {
            $query->with(['customer', 'payments'])->latest()->limit(50);
        }]);

        // Update sales data to ensure it's current
        $shift->updateSalesData();

        $paymentSummary = [
            'cash' => $shift->cash_sales,
            'card' => $shift->card_sales,
            'mobile_money' => $shift->mobile_sales,
            'credit' => $shift->credit_sales,
            'total' => $shift->total_sales,
        ];

        return view('shifts.show', compact('shift', 'paymentSummary'));
    }

    public function end(Request $request, Shift $shift)
    {
        if ($shift->status !== 'active') {
            return back()->with('error', 'Cannot end a shift that is not active.');
        }

        if ($shift->user_id !== auth()->id() && !auth()->user()->hasPermission('manage_shifts')) {
            return back()->with('error', 'You can only end your own shifts.');
        }

        $request->validate([
            'ending_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Update sales data one final time
            $shift->updateSalesData();

            // End the shift
            $shift->endShift($request->ending_cash, $request->notes);

            DB::commit();

            return redirect()->route('shifts.show', $shift)
                ->with('success', 'Shift ended successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error ending shift: ' . $e->getMessage());
        }
    }

    public function addCashDrop(Request $request, Shift $shift)
    {
        if ($shift->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add cash drop to inactive shift'
            ]);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $shift->current_cash,
            'reason' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $cashDrop = CashDrop::create([
                'shift_id' => $shift->id,
                'amount' => $request->amount,
                'reason' => $request->reason,
                'user_id' => auth()->id(),
            ]);

            // Update shift expected cash
            $shift->updateSalesData();

            DB::commit();

            return response()->json([
                'success' => true,
                'cash_drop' => $cashDrop,
                'current_cash' => $shift->current_cash,
                'message' => 'Cash drop recorded successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error recording cash drop: ' . $e->getMessage()
            ]);
        }
    }

    public function suspend(Shift $shift)
    {
        if ($shift->status !== 'active') {
            return back()->with('error', 'Cannot suspend a shift that is not active.');
        }

        $shift->update(['status' => 'suspended']);

        return back()->with('success', 'Shift suspended successfully!');
    }

    public function resume(Shift $shift)
    {
        if ($shift->status !== 'suspended') {
            return back()->with('error', 'Cannot resume a shift that is not suspended.');
        }

        $shift->update(['status' => 'active']);

        return back()->with('success', 'Shift resumed successfully!');
    }

    public function report(Shift $shift)
    {
        $shift->load(['user', 'cashDrops.user', 'sales.customer']);
        $shift->updateSalesData();

        // Generate Z-Report data
        $zReport = [
            'shift_number' => $shift->shift_number,
            'user_name' => $shift->user->name,
            'start_time' => $shift->started_at->format('Y-m-d H:i:s'),
            'end_time' => $shift->ended_at ? $shift->ended_at->format('Y-m-d H:i:s') : 'Active',
            'duration' => $shift->duration,
            'starting_cash' => $shift->starting_cash,
            'ending_cash' => $shift->ending_cash,
            'expected_cash' => $shift->expected_cash,
            'cash_difference' => $shift->cash_difference,
            'is_balanced' => $shift->is_balanced,
            'cash_drops' => $shift->cashDrops,
            'payment_summary' => [
                'Cash' => $shift->cash_sales,
                'Card' => $shift->card_sales,
                'Mobile Money' => $shift->mobile_sales,
                'Credit' => $shift->credit_sales,
            ],
            'transaction_count' => $shift->transaction_count,
            'total_sales' => $shift->total_sales,
            'refunds_count' => $shift->refunds_count,
            'refunds_amount' => $shift->refunds_amount,
            'net_sales' => $shift->total_sales - $shift->refunds_amount,
        ];

        return view('shifts.report', compact('shift', 'zReport'));
    }

    public function getUserShifts(User $user)
    {
        $shifts = $user->shifts()
            ->withCount('sales')
            ->latest()
            ->paginate(15);

        $stats = [
            'total_shifts' => $user->shifts()->count(),
            'total_sales' => $user->shifts()->sum('total_sales'),
            'avg_shift_sales' => $user->shifts()->ended()->avg('total_sales'),
            'current_shift' => $user->activeShift,
        ];

        return view('shifts.user-shifts', compact('user', 'shifts', 'stats'));
    }
}