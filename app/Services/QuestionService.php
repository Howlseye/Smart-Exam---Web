<?php

namespace App\Services;

use App\Models\Question;

class QuestionService
{
    /**
     * Get all unanswered questions.
     */
    public function getUnansweredQuestions()
    {
        // Ambil data soal yang belum dijawab.
        return Question::select('id', 'type', 'question', 'options')
            ->where('is_answered', false)
            ->get();
    }

    /**
     * Submit student answers.
     */
    public function submitStudentAnswers(array $answers): int
    {
        $updatedCount = 0;

        foreach ($answers as $answerData) {
            $question = Question::find($answerData['id']);
            
            // Cek dan simpan jika belum dijawab.
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
