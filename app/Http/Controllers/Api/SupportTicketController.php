<?php

namespace App\Http\Controllers\Api;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupportTicketController extends Controller
{
    public function index()
    {
        return SupportTicket::all();
    }
    public function store(Request $request)
    {
        return SupportTicket::create($request->all());
    }
    public function show($id)
    {
        return SupportTicket::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $t = SupportTicket::findOrFail($id);
        $t->update($request->all());
        return $t;
    }
    public function destroy($id)
    {
        SupportTicket::destroy($id);
        return response()->noContent();
    }
}
