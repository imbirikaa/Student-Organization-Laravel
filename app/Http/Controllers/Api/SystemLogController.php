<?php

namespace App\Http\Controllers\Api;

use App\Models\SystemLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SystemLogController extends Controller
{
    public function index()
    {
        return SystemLog::all();
    }
    public function store(Request $request)
    {
        return SystemLog::create($request->all());
    }
    public function show($id)
    {
        return SystemLog::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $log = SystemLog::findOrFail($id);
        $log->update($request->all());
        return $log;
    }
    public function destroy($id)
    {
        SystemLog::destroy($id);
        return response()->noContent();
    }
}
