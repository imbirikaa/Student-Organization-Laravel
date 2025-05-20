<?php

namespace App\Http\Controllers\Api;

use App\Models\University;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UniversityController extends Controller
{
    public function index()
    {
        return University::all();
    }
    public function store(Request $request)
    {
        return University::create($request->all());
    }
    public function show($id)
    {
        return University::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $u = University::findOrFail($id);
        $u->update($request->all());
        return $u;
    }
    public function destroy($id)
    {
        University::destroy($id);
        return response()->noContent();
    }
}
