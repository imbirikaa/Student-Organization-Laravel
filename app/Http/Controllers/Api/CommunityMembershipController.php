<?php

namespace App\Http\Controllers\Api;

use App\Models\CommunityMembership;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommunityMembershipController extends Controller
{
    public function index()
    {
        return CommunityMembership::all();
    }
    public function store(Request $request)
    {
        return CommunityMembership::create($request->all());
    }
    public function show($id)
    {
        return CommunityMembership::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $record = CommunityMembership::findOrFail($id);
        $record->update($request->all());
        return $record;
    }
    public function destroy($id)
    {
        CommunityMembership::destroy($id);
        return response()->noContent();
    }
}
