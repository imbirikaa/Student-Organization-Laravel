<?php

namespace App\Http\Controllers\Api;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MessageController extends Controller
{
    public function index()
    {
        return Message::all();
    }
    public function store(Request $request)
    {
        return Message::create($request->all());
    }
    public function show($id)
    {
        return Message::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $msg = Message::findOrFail($id);
        $msg->update($request->all());
        return $msg;
    }
    public function destroy($id)
    {
        Message::destroy($id);
        return response()->noContent();
    }
}
