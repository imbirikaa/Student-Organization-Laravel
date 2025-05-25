<?php

namespace App\Http\Controllers\Api;

use App\Models\UserRole;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserRoleController extends Controller
{
    public function index()
    {
        return UserRole::all();
    }
    public function store(Request $request)
    {
        return UserRole::create($request->all());
    }
    public function show($id)
    {
        return UserRole::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $role = UserRole::findOrFail($id);
        $role->update($request->all());
        return $role;
    }
    public function destroy($id)
    {
        UserRole::destroy($id);
        return response()->noContent();
    }
}
