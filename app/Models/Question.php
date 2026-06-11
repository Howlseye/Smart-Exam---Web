<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
        'is_answered' => 'boolean',
    ];

    protected static function booted()
    {
        static::saved(function ($question) {
            // Jika essay (type 2) dan terjawab
            if ($question->type == 2 && !empty($question->student_answer)) {
                $existingQueue = \App\Models\AIQueue::where('question', $question->question)->first();
                
                $hasLog = false;
                if ($existingQueue) {
                    $hasLog = \App\Models\AIQueueLog::where('queue_id', $existingQueue->id)->exists();
                }
                
                // Jika belum ada antrean, atau antrean gagal dan belum ada log yang sukses
                if (!$existingQueue || (!$hasLog && $existingQueue->status === 'failed')) {
                    $isPending = $existingQueue && $existingQueue->status === 'pending';
                    
                    if (!$isPending) {
                        \App\Models\AIQueue::create([
                            'question' => $question->question,
                            'answer' => $question->student_answer,
                            'status' => 'pending'
                        ]);
                    }
                }
            }
        });
    }
}
