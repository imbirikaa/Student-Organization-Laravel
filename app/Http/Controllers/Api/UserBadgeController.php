<?php

namespace App\Http\Controllers\Api;

use App\Models\UserBadge;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserBadgeController extends Controller
{
    public function index()
    {
        return UserBadge::all();
    }
    public function store(Request $request)
    {
        return UserBadge::create($request->all());
    }
    public function show($id)
    {
        return UserBadge::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $badge = UserBadge::findOrFail($id);
        $badge->update($request->all());
        return $badge;
    }
    public function destroy($id)
    {
        UserBadge::destroy($id);
        return response()->noContent();
    }
}
