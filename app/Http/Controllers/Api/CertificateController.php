<?php

namespace App\Http\Controllers\Api;

use App\Models\Certificate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CertificateController extends Controller
{
    public function index()
    {
        return Certificate::all();
    }
    public function store(Request $request)
    {
        return Certificate::create($request->all());
    }
    public function show($id)
    {
        return Certificate::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $cert = Certificate::findOrFail($id);
        $cert->update($request->all());
        return $cert;
    }
    public function destroy($id)
    {
        Certificate::destroy($id);
        return response()->noContent();
    }
}
