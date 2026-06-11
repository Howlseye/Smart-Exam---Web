<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QuestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    protected QuestionService $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    // Ambil daftar soal yang belum dijawab.
    public function index()
    {
        $questions = $this->questionService->getUnansweredQuestions();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data soal',
            'data' => $questions
        ]);
    }

    // Simpan jawaban siswa yang dikirimkan.
    public function submitAnswers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array|max:50',
            'answers.*.id' => 'required|exists:questions,id',
            'answers.*.answer' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format pengiriman salah atau ID tidak valid.'
            ], 400);
        }

        $answers = $validator->validated()['answers'];
        
        $updatedCount = $this->questionService->submitStudentAnswers($answers);

        return response()->json([
            'success' => true,
            'message' => "Berhasil menyimpan {$updatedCount} jawaban dan antrean."
        ]);
    }
}
