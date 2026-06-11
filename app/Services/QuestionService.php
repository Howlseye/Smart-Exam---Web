<?php

namespace App\Services;

use App\Models\Question;

class QuestionService
{
    // Ambil semua soal yang belum dijawab.
    public function getUnansweredQuestions()
    {
        return Question::select('id', 'type', 'question', 'options')
            ->where('is_answered', false)
            ->get();
    }

    // Simpan jawaban siswa yang baru.
    public function submitStudentAnswers(array $answers): int
    {
        $updatedCount = 0;

        foreach ($answers as $answerData) {
            $question = Question::find($answerData['id']);
            
            // Simpan jika soal belum dijawab.
            if ($question && !$question->is_answered) {
                $question->update([
                    'student_answer' => $answerData['answer'],
                    'is_answered' => true
                ]);
                
                $updatedCount++;
            }
        }

        return $updatedCount;
    }
}
