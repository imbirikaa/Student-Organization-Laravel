<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\CommunityRole;
use Illuminate\Http\Request;

class CommunityRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Community $community)
    {

        $this->authorize('manage-community-roles', $community);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Create the new role and link it to the specific community
        $role = $community->roles()->create($validatedData);

        return response()->json($role, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getAssignableRoles(Community $community)
    {
        // Get roles where community_id is NULL (global roles)
        $globalRoles = CommunityRole::whereNull('community_id')->get();

        // Get roles specific to this community
        $customRoles = CommunityRole::where('community_id', $community->id)->get();

        // Merge the two collections together
        $assignableRoles = $globalRoles->merge($customRoles);

        return response()->json($assignableRoles);
    }
    
}
