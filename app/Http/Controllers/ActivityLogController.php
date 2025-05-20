<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (!Gate::allows('view-logs')) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        $query = ActivityLog::with('user');

        // Filter by user if provided
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action if provided
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->whereDate('logged_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('logged_at', '<=', $request->to_date);
        }

        $logs = $query->orderBy('logged_at', 'desc')->paginate(15);

        return response()->json([
            'data' => $logs,
        ]);
    }

    /**
     * Display the specified activity log.
     *
     * @param  \App\Models\ActivityLog  $log
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ActivityLog $log)
    {
        if (!Gate::allows('view-logs')) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        return response()->json([
            'data' => $log->load('user'),
        ]);
    }
}
