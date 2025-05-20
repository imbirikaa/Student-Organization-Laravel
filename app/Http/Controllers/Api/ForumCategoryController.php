<?php

namespace App\Http\Controllers\Api;

use App\Models\ForumCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ForumCategoryController extends Controller
{
    public function index()
    {
        return ForumCategory::all();
    }
    public function store(Request $request)
    {
        return ForumCategory::create($request->all());
    }
    public function show($id)
    {
        return ForumCategory::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $item = ForumCategory::findOrFail($id);
        $item->update($request->all());
        return $item;
    }
    public function destroy($id)
    {
        ForumCategory::destroy($id);
        return response()->noContent();
    }
}
