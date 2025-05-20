<?php

namespace App\Http\Controllers\Api;

use App\Models\Badge;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BadgeController extends Controller
{
    public function index()
    {
        return Badge::all();
    }
    public function store(Request $request)
    {
        return Badge::create($request->all());
    }
    public function show($id)
    {
        return Badge::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $badge = Badge::findOrFail($id);
        $badge->update($request->all());
        return $badge;
    }
    public function destroy($id)
    {
        Badge::destroy($id);
        return response()->noContent();
    }
}
