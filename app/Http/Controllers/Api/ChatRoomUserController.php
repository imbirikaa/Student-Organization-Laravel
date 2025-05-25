<?php

namespace App\Http\Controllers\Api;

use App\Models\ChatRoomUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatRoomUserController extends Controller
{
    public function index()
    {
        return ChatRoomUser::all();
    }
    public function store(Request $request)
    {
        return ChatRoomUser::create($request->all());
    }
    public function show($id)
    {
        return ChatRoomUser::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $item = ChatRoomUser::findOrFail($id);
        $item->update($request->all());
        return $item;
    }
    public function destroy($id)
    {
        ChatRoomUser::destroy($id);
        return response()->noContent();
    }
}
