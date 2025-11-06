<?php
// app/Http/Controllers/AuditLogController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_audit_logs');
    }

    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->latest();

        // Filter by date
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('causer_id', $request->user_id);
        }

        // Filter by event type
        if ($request->has('event') && $request->event) {
            $query->where('event', $request->event);
        }

        $activities = $query->paginate(50);

        $stats = [
            'total' => Activity::count(),
            'today' => Activity::whereDate('created_at', today())->count(),
            'users' => Activity::distinct('causer_id')->count('causer_id'),
        ];

        $users = \App\Models\User::where('is_active', true)->get();
        $events = ['created', 'updated', 'deleted', 'logged in', 'logged out'];

        return view('audit.index', compact('activities', 'stats', 'users', 'events'));
    }

    public function show(Activity $activity)
    {
        $activity->load(['causer', 'subject']);
        
        return view('audit.show', compact('activity'));
    }

    public function userActivity($userId)
    {
        $user = \App\Models\User::findOrFail($userId);
        
        $activities = Activity::where('causer_id', $userId)
            ->with('subject')
            ->latest()
            ->paginate(50);

        return view('audit.user-activity', compact('user', 'activities'));
    }
}