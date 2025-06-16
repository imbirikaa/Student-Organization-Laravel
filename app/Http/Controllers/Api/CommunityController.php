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
        $communities = Community::withCount(['memberships', 'events'])
            ->latest()
            ->get()
            ->map(function ($community) {
                return [
                    'id' => $community->id,
                    'community' => $community->community,
                    'logo' => $community->logo_url, // Use the computed logo URL
                    'about' => $community->about,
                    'founding_year' => $community->founding_year,
                    'memberships_count' => $community->memberships_count,
                    'events_count' => $community->events_count,
                    'created_at' => $community->created_at
                ];
            });

        return response()->json(['communities' => $communities]);
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

            $community = Community::create($validatedData);

            // Handle logo upload after community is created
            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $originalName = $file->getClientOriginalName();
                $filename = time() . '_' . $originalName;
                $path = 'communities/' . $community->id . '/logo/' . $filename;
                
                $storedPath = $file->storeAs(dirname($path), basename($path), 'public');

                // Create file upload record
                $fileUpload = $community->fileUploads()->create([
                    'original_name' => $originalName,
                    'filename' => $filename,
                    'path' => $storedPath,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'disk' => 'public',
                    'uploadable_type' => get_class($community),
                    'uploadable_id' => $community->id,
                    'upload_type' => 'cover_image',
                    'uploaded_by' => Auth::id(),
                    'is_public' => true,
                    'description' => 'Community Logo'
                ]);

                // Update community with logo URL
                $community->update(['logo' => Storage::url($storedPath)]);
            }

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
                
                // Also delete the old file upload record
                $community->fileUploads()->where('upload_type', 'cover_image')->delete();
            }

            $file = $request->file('logo');
            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . $originalName;
            $path = 'communities/' . $community->id . '/logo/' . $filename;
            
            $storedPath = $file->storeAs(dirname($path), basename($path), 'public');

            // Create file upload record
            $fileUpload = $community->fileUploads()->create([
                'original_name' => $originalName,
                'filename' => $filename,
                'path' => $storedPath,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'disk' => 'public',
                'uploadable_type' => get_class($community),
                'uploadable_id' => $community->id,
                'upload_type' => 'cover_image',
                'uploaded_by' => $user->id,
                'is_public' => true,
                'description' => 'Community Logo'
            ]);

            $validatedData['logo'] = Storage::url($storedPath);
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
