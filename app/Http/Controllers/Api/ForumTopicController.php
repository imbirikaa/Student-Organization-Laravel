<?php

namespace App\Http\Controllers\Api;

use App\Models\ForumTopic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ForumTopicController extends Controller
{
    public function index()
    {
        return ForumTopic::all();
    }
    public function store(Request $request)
    {
        return ForumTopic::create($request->all());
    }
    public function show($id)
    {
        return ForumTopic::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $topic = ForumTopic::findOrFail($id);
        $topic->update($request->all());
        return $topic;
    }
    public function destroy($id)
    {
        ForumTopic::destroy($id);
        return response()->noContent();
    }
}
