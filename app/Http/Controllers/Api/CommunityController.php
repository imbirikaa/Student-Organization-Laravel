<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\CommunityMembership; // <-- Added this
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Added this
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class CommunityController extends Controller
{
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
            $validatedData['creator_id'] = auth()->user()->id;

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
        return $community;
    }

    /**
     * Update the specified resource in storage.
     * EDITED: Now uses Route-Model Binding.
     */
    public function update(Request $request, Community $community)
    {
        // Add validation for the update request as needed
        $community->update($request->all());
        return $community;
    }

    /**
     * Remove the specified resource from storage.
     * EDITED: Now uses Route-Model Binding.
     */
    public function destroy(Community $community)
    {
        // Consider deleting the community logo from storage as well
        // if ($community->logo) { ... }
        $community->delete();
        return response()->noContent();
    }

    public function userCommunityCount()
    {
        return response()->json(['count' => Community::count()]);
    }
}
