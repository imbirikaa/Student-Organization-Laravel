<?php

namespace App\Http\Controllers\Api;

use App\Models\ForumPost;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ForumPostController extends Controller
{
    public function index()
    {
        return ForumPost::all();
    }
    public function store(Request $request)
    {
        return ForumPost::create($request->all());
    }
    public function show($id)
    {
        return ForumPost::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $post = ForumPost::findOrFail($id);
        $post->update($request->all());
        return $post;
    }
    public function destroy($id)
    {
        ForumPost::destroy($id);
        return response()->noContent();
    }
}
