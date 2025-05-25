<?php

namespace App\Http\Controllers\Api;

use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupportTicketMessageController extends Controller
{
    public function index()
    {
        return SupportTicketMessage::all();
    }
    public function store(Request $request)
    {
        return SupportTicketMessage::create($request->all());
    }
    public function show($id)
    {
        return SupportTicketMessage::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $msg = SupportTicketMessage::findOrFail($id);
        $msg->update($request->all());
        return $msg;
    }
    public function destroy($id)
    {
        SupportTicketMessage::destroy($id);
        return response()->noContent();
    }
}
