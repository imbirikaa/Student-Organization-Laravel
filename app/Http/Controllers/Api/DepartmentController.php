<?php

namespace App\Http\Controllers\Api;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    public function index()
    {
        return Department::all();
    }
    public function store(Request $request)
    {
        return Department::create($request->all());
    }
    public function show($id)
    {
        return Department::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $dep = Department::findOrFail($id);
        $dep->update($request->all());
        return $dep;
    }
    public function destroy($id)
    {
        Department::destroy($id);
        return response()->noContent();
    }
}
