<?php

namespace App\Http\Controllers\Api;

use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuizAnswerController extends Controller
{
    public function index()
    {
        return QuizAnswer::all();
    }
    public function store(Request $request)
    {
        return QuizAnswer::create($request->all());
    }
    public function show($id)
    {
        return QuizAnswer::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $a = QuizAnswer::findOrFail($id);
        $a->update($request->all());
        return $a;
    }
    public function destroy($id)
    {
        QuizAnswer::destroy($id);
        return response()->noContent();
    }
}
