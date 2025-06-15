<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Traits\HasCommunityPermissions;
use App\Traits\LogsAuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class CommunityController extends Controller
{
    use HasCommunityPermissions, LogsAuditTrail;

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Community::withCount(['memberships', 'events'])->latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'community' => 'required|string|max:255|unique:communities,community',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'founding_year' => 'nullable|digits:4|integer|min:1800|max:' . date('Y'),
                'contact_email' => 'nullable|email|max:255',
                'about' => 'nullable|string',
                'mission' => 'nullable|string',
                'vision' => 'nullable|string',
                'achievements' => 'nullable|string',
                'traditional_events' => 'nullable|string',
                'sponsors' => 'nullable|string',
                'faq' => 'nullable|string',
            ]);

            // --- EDITED: Set the creator to the currently logged-in user ---
            $validatedData['creator_id'] = Auth::id();

            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('community_logos', 'public');
                $validatedData['logo'] = Storage::url($path);
            }

            $community = Community::create($validatedData);

            // --- EDITED: Automatically make the creator an admin member ---
            CommunityMembership::create([
                'user_id' => $community->creator_id,
                'community_id' => $community->id,
                'role_id' => 1, // Assuming '1' is the ID for an 'Admin' or 'Creator' role
                'status' => 'approved',
            ]);
            // -----------------------------------------------------------

            return response()->json($community, 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An unexpected server error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * EDITED: Now uses Route-Model Binding for efficiency.
     */
    public function show(Community $community)
    {
        return $community->loadCount(['memberships', 'events']);
    }

    /**
     * Update the specified resource in storage.
     * EDITED: Now uses Route-Model Binding and permission checks.
     */
    public function update(Request $request, Community $community)
    {
        $user = $request->user();
        
        // Check permission to edit community
        $permissionError = $this->requireCommunityPermission($user, $community->id, 'edit_community');
        if ($permissionError) {
            $this->logPermissionAction('community_update', $community->id, 'edit_community', false, null, $request);
            return $permissionError;
        }

        $oldValues = $community->toArray();
        
        $validatedData = $request->validate([
            'community' => 'sometimes|string|max:255|unique:communities,community,' . $community->id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'founding_year' => 'nullable|digits:4|integer|min:1800|max:' . date('Y'),
            'contact_email' => 'nullable|email|max:255',
            'about' => 'nullable|string',
            'mission' => 'nullable|string',
            'vision' => 'nullable|string',
            'achievements' => 'nullable|string',
            'traditional_events' => 'nullable|string',
            'sponsors' => 'nullable|string',
            'faq' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($community->logo) {
                $oldPath = str_replace('/storage/', '', $community->logo);
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $request->file('logo')->store('community_logos', 'public');
            $validatedData['logo'] = Storage::url($path);
        }

        $community->update($validatedData);
        
        // Log the action
        $this->logCommunityAction(
            'community_updated',
            $community->id,
            $oldValues,
            $community->fresh()->toArray(),
            "Community '{$community->community}' updated",
            $request
        );

        return response()->json([
            'message' => 'Community updated successfully',
            'community' => $community->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * EDITED: Now uses Route-Model Binding and permission checks.
     */
    public function destroy(Community $community)
    {
        $user = request()->user();
        
        // Check permission to delete community (only founders can delete)
        $permissionError = $this->requireCommunityPermission($user, $community->id, 'delete_community');
        if ($permissionError) {
            $this->logPermissionAction('community_delete', $community->id, 'delete_community', false, null, request());
            return $permissionError;
        }

        $communityData = $community->toArray();
        
        // Delete community logo from storage if exists
        if ($community->logo) {
            $logoPath = str_replace('/storage/', '', $community->logo);
            Storage::disk('public')->delete($logoPath);
        }
        
        // Log the action before deletion
        $this->logCommunityAction(
            'community_deleted',
            $community->id,
            $communityData,
            null,
            "Community '{$community->community}' deleted",
            request()
        );
        
        $community->delete();
        
        return response()->json(['message' => 'Community deleted successfully']);
    }

    public function userCommunityCount()
    {
        return response()->json(['count' => Community::count()]);
    }
}
