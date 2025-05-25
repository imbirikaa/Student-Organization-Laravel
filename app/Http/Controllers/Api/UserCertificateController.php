<?php

namespace App\Http\Controllers\Api;

use App\Models\UserCertificate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserCertificateController extends Controller
{
    public function index()
    {
        return UserCertificate::all();
    }
    public function store(Request $request)
    {
        return UserCertificate::create($request->all());
    }
    public function show($id)
    {
        return UserCertificate::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $cert = UserCertificate::findOrFail($id);
        $cert->update($request->all());
        return $cert;
    }
    public function destroy($id)
    {
        UserCertificate::destroy($id);
        return response()->noContent();
    }
}
