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

    public function index()
    {
        // Ambil data melalui layanan.
        $questions = $this->questionService->getUnansweredQuestions();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data soal',
            'data' => $questions
        ]);
    }

    public function submitAnswers(Request $request)
    {
        // Validasi input jawaban.
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array|max:50',
            'answers.*.id' => 'required|exists:questions,id',
            'answers.*.answer' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format pengiriman salah atau terdapat ID soal yang tidak valid.'
            ], 400);
        }

        $answers = $validator->validated()['answers'];
        
        // Simpan jawaban melalui layanan.
        $updatedCount = $this->questionService->submitStudentAnswers($answers);

        return response()->json([
            'success' => true,
            'message' => "Berhasil menyimpan jawaban untuk {$updatedCount} soal dan menambahkannya ke antrean AI."
        ]);
    }
}
