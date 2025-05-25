<?php

namespace App\Http\Controllers\Api;

use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuizQuestionController extends Controller
{
    public function index()
    {
        return QuizQuestion::all();
    }
    public function store(Request $request)
    {
        return QuizQuestion::create($request->all());
    }
    public function show($id)
    {
        return QuizQuestion::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $q = QuizQuestion::findOrFail($id);
        $q->update($request->all());
        return $q;
    }
    public function destroy($id)
    {
        QuizQuestion::destroy($id);
        return response()->noContent();
    }
}
