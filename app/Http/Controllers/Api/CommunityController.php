<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Community;
use Illuminate\Http\Request;


class CommunityController extends Controller
{
    public function index()
    {
        return Community::all();
    }

    public function store(Request $request)
    {
        return Community::create($request->all());
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
