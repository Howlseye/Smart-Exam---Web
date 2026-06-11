<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        // Mengambil seluruh data soal (id, tipe, pertanyaan, pilihan) yang belum dijawab
        $questions = \App\Models\Question::select('id', 'type', 'question', 'options')
            ->where('is_answered', false)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil data soal',
            'data' => $questions
        ]);
    }

    public function submitAnswers(Request $request)
    {
        // Validasi input: berupa array 'answers' maksimal 50 item
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
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
        $updatedCount = 0;

        foreach ($answers as $answerData) {
            $question = \App\Models\Question::find($answerData['id']);
            
            if ($question && !$question->is_answered) {
                $question->update([
                    'student_answer' => $answerData['answer'],
                    'is_answered' => true
                ]);
                

                $updatedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menyimpan jawaban untuk {$updatedCount} soal dan menambahkannya ke antrean AI."
        ]);
    }
}
