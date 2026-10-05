<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    /**
     * Display the audit trail.
     */
    public function index(Request $request): Response
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

        $logs->through(fn (ActivityLog $log) => [
            'id' => $log->id,
            'createdAt' => $log->created_at?->format('d M Y h:i A'),
            'userName' => $log->user?->name ?? 'System',
            'action' => $log->action,
            'actionLabel' => Str::of($log->action)->afterLast('.')->headline()->toString(),
            'recordType' => Str::title(str_replace('_', ' ', $log->model_type ? class_basename($log->model_type) : 'N/A')),
            'recordId' => $log->model_id,
            'detailKeys' => array_slice(array_keys($log->payload ?? []), 0, 4),
            'detailCount' => count($log->payload ?? []),
            'ipAddress' => $log->ip_address ?? 'N/A',
        ]);

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => [
                'data' => $logs->items(),
                'currentPage' => $logs->currentPage(),
                'lastPage' => $logs->lastPage(),
                'firstItem' => $logs->firstItem(),
                'lastItem' => $logs->lastItem(),
                'total' => $logs->total(),
                'previousPageUrl' => $logs->previousPageUrl(),
                'nextPageUrl' => $logs->nextPageUrl(),
            ],
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'action' => (string) $request->query('action', ''),
            ],
            'availableActions' => $availableActions,
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.activity-logs.index'),
            ],
        ]);
    }
}
