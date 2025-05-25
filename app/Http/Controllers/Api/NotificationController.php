<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        return Notification::all();
    }
    public function store(Request $request)
    {
        return Notification::create($request->all());
    }
    public function show($id)
    {
        return Notification::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $n = Notification::findOrFail($id);
        $n->update($request->all());
        return $n;
    }
    public function destroy($id)
    {
        Notification::destroy($id);
        return response()->noContent();
    }
}
