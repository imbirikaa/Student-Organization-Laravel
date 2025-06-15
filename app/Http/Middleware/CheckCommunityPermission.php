<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\HasCommunityPermissions;
use Symfony\Component\HttpFoundation\Response;

class CheckCommunityPermission
{
    use HasCommunityPermissions;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission, string $communityParam = 'community'): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Authentication required'], 401);
        }

        // Get community ID from route parameters
        $communityId = $request->route($communityParam);
        
        if (!$communityId) {
            return response()->json(['message' => 'Community not specified'], 400);
        }

        // Check permission
        $errorResponse = $this->requireCommunityPermission($user, $communityId, $permission);
        
        if ($errorResponse) {
            return $errorResponse;
        }

        return $next($request);
    }
}
