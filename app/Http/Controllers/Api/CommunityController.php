<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception; // <-- Make sure this line is present

class CommunityController extends Controller
{

    public function index()
    {
        return Community::all();
    }

    public function store(Request $request)
    {
        // This try-catch block is essential.
        try {
            $validator = Validator::make($request->all(), [
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

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('community_logos', 'public');
                $data['logo'] = Storage::url($path);
            }

            $community = Community::create($data);

            return response()->json($community, 201);

        } catch (Exception $e) {
            // This block will catch ANY error and return a proper JSON response,
            // preventing the HTML error page from being sent.
            return response()->json([
                'message' => 'An unexpected server error occurred.',
                'error' => $e->getMessage() // This gives the specific error message
            ], 500);
        }
    }

    public function show($id)
    {
        return Community::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        $community->update($request->all());
        return $community;
    }

    public function destroy($id)
    {
        Community::destroy($id);
        return response()->noContent();
    }
    public function userCommunityCount()
    {
        return response()->json(['count' => Community::count()]);
    }
}
