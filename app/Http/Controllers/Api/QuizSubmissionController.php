<?php

namespace App\Http\Controllers\Api;

use App\Models\QuizSubmission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuizSubmissionController extends Controller
{
    public function index()
    {
        return QuizSubmission::all();
    }
    public function store(Request $request)
    {
        return QuizSubmission::create($request->all());
    }
    public function show($id)
    {
        return QuizSubmission::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $s = QuizSubmission::findOrFail($id);
        $s->update($request->all());
        return $s;
    }
    public function destroy($id)
    {
        QuizSubmission::destroy($id);
        return response()->noContent();
    }
}
