<?php

namespace App\Http\Controllers\Api;

use App\Models\Quiz;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuizController extends Controller
{
    public function index()
    {
        return Quiz::all();
    }
    public function store(Request $request)
    {
        return Quiz::create($request->all());
    }
    public function show($id)
    {
        return Quiz::findOrFail($id);
    }
    public function update(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->update($request->all());
        return $quiz;
    }
    public function destroy($id)
    {
        Quiz::destroy($id);
        return response()->noContent();
    }
}
