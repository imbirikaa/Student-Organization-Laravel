<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Traits\HasCommunityPermissions;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    use HasCommunityPermissions;

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get audit logs for a specific community
     */
    public function getCommunityAuditLogs(Request $request, $communityId)
    {
        $user = $request->user();

        // Check if user has permission to view audit logs
        $permissionError = $this->requireCommunityPermission($user, $communityId, 'view_audit_logs');
        if ($permissionError) {
            return $permissionError;
        }

        $query = AuditLog::with(['user', 'community'])
            ->forCommunity($communityId)
            ->orderBy('created_at', 'desc');

        // Filter by action if provided
        if ($request->has('action')) {
            $query->byAction($request->action);
        }

        // Filter by resource type if provided
        if ($request->has('resource_type')) {
            $query->byResourceType($request->resource_type);
        }

        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        $perPage = $request->input('per_page', 50);
        $logs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'audit_logs' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'last_page' => $logs->lastPage(),
            ]
        ]);
    }

    /**
     * Get available audit log actions for filtering
     */
    public function getAuditActions(Request $request, $communityId)
    {
        $user = $request->user();

        // Check if user has permission to view audit logs
        $permissionError = $this->requireCommunityPermission($user, $communityId, 'view_audit_logs');
        if ($permissionError) {
            return $permissionError;
        }

        $actions = AuditLog::forCommunity($communityId)
            ->distinct()
            ->pluck('action')
            ->sort()
            ->values();

        return response()->json([
            'success' => true,
            'actions' => $actions
        ]);
    }

    /**
     * Get audit statistics for a community
     */
    public function getAuditStats(Request $request, $communityId)
    {
        $user = $request->user();

        // Check if user has permission to view audit logs
        $permissionError = $this->requireCommunityPermission($user, $communityId, 'view_audit_logs');
        if ($permissionError) {
            return $permissionError;
        }

        $stats = [
            'total_actions' => AuditLog::forCommunity($communityId)->count(),
            'actions_today' => AuditLog::forCommunity($communityId)
                ->whereDate('created_at', today())
                ->count(),
            'actions_this_week' => AuditLog::forCommunity($communityId)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'top_actions' => AuditLog::forCommunity($communityId)
                ->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'recent_activity' => AuditLog::with(['user'])
                ->forCommunity($communityId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
