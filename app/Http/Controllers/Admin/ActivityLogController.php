<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display the audit trail.
     */
    public function index(Request $request)
    {
        $action = $request->filled('action') ? str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $request->action) : null;
        $search = $request->filled('search') ? str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $request->search) : null;

        $logs = ActivityLog::with('user')
            ->when($action, function ($query) use ($action) {
                return $query->where('action', 'like', '%'.$action.'%');
            })
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('user', function ($user) use ($search) {
                    $user->where('name', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $availableActions = ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('backend.activity-logs.index', compact('logs', 'availableActions'));
    }
}
