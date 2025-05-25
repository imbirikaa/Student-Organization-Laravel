<?php

namespace App\Http\Controllers\Api;

use App\Models\ChatRoom;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatRoomController extends Controller
{
    public function index() { return ChatRoom::all(); }
    public function store(Request $request) { return ChatRoom::create($request->all()); }
    public function show($id) { return ChatRoom::findOrFail($id); }
    public function update(Request $request, $id) {
        $chat = ChatRoom::findOrFail($id);
        $chat->update($request->all());
        return $chat;
    }
    public function destroy($id) {
        ChatRoom::destroy($id);
        return response()->noContent();
    }
}
