<?php

namespace App\Services;

use App\Models\Question;
use App\Models\AIQueue;
use App\Models\AIQueueLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class AIQueueService
{
    /**
     * Sync missing questions.
     */
    public function syncMissingQuestions(): int
    {
        // Ambil soal essay yang sudah terjawab.
        $questions = Question::where('type', 2)
            ->whereNotNull('student_answer')
            ->where('student_answer', '!=', '')
            ->get();

        $syncedCount = 0;

        foreach ($questions as $question) {
            $existingQueue = AIQueue::where('question', $question->question)->first();
            
            $hasLog = false;
            if ($existingQueue) {
                $hasLog = AIQueueLog::where('queue_id', $existingQueue->id)->exists();
            }
            
            // Periksa kondisi antrean saat ini.
            if (!$existingQueue || (!$hasLog && $existingQueue->status === 'failed')) {
                $isPending = $existingQueue && $existingQueue->status === 'pending';
                
                if (!$isPending) {
                    // Buat antrean baru.
                    AIQueue::create([
                        'question' => $question->question,
                        'answer' => $question->student_answer,
                        'status' => 'pending'
                    ]);
                    $syncedCount++;
                }
            }
        }

        return $syncedCount;
    }

    /**
     * Get next pending queue.
     */
    public function takeNextQueue(): ?AIQueue
    {
        // Ambil antrean pending pertama.
        $queue = AIQueue::where('status', 'pending')->first();

        if ($queue) {
            // Tandai antrean sedang diproses.
            $queue->update(['status' => 'processing']);
        }

        return $queue;
    }

    /**
     * Process AI Queue.
     */
    public function processQueue(int $id): array
    {
        $queue = AIQueue::find($id);
        
        // Pastikan status antrean valid.
        if (!$queue || $queue->status !== 'processing') {
            return ['status' => 'error', 'message' => 'Antrean tidak valid.'];
        }

        $apiKey = config('services.groq.api_key');
        // Buat prompt untuk AI.
        $prompt = "Tolong berikan nilai untuk jawaban esai berikut.\n\n" .
                  "Pertanyaan: {$queue->question}\n\n" .
                  "Jawaban: {$queue->answer}\n\n" .
                  "Berikan respons dalam format JSON dengan struktur: {\"score\": [angka 0-100], \"confidence\": \"[rendah/sedang/tinggi]\"}. Jangan tambahkan teks lain selain JSON.";

        $startTime = microtime(true);
        try {
            // Panggil Groq API.
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.3,
                ]);

            $endTime = microtime(true);
            $processingTime = round($endTime - $startTime);

            if ($response->successful()) {
                $responseData = $response->json();
                $aiResponseText = $responseData['choices'][0]['message']['content'] ?? '{}';
                
                // Bersihkan tag markdown.
                $aiResponseText = preg_replace('/```json\s*(.*?)\s*```/s', '$1', $aiResponseText);
                $parsedResponse = json_decode($aiResponseText, true);

                $score = $parsedResponse['score'] ?? 0;
                $confidence = $parsedResponse['confidence'] ?? 'rendah';

                // Catat log hasil AI.
                AIQueueLog::create([
                    'queue_id' => $queue->id,
                    'attempt' => 1,
                    'confidence' => $confidence,
                    'score' => $score,
                    'ai_response' => $aiResponseText,
                    'status' => 'completed',
                    'processing_time' => $processingTime,
                ]);

                // Selesaikan proses antrean.
                $queue->update(['status' => 'completed']);

                return [
                    'status' => 'success',
                    'data' => [
                        'id' => $queue->id,
                        'score' => $score,
                        'ai_response' => Str::limit($aiResponseText, 50),
                        'processing_time' => $processingTime,
                        'queue_status' => 'completed'
                    ]
                ];
            } else {
                throw new Exception("API Error: " . $response->body());
            }
        } catch (Exception $e) {
            $endTime = microtime(true);
            $processingTime = round($endTime - $startTime);

            // Cek jika terkena rate limit.
            if (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'quota')) {
                $queue->update(['status' => 'pending']);
                return [
                    'status' => 'rate_limit', 
                    'message' => 'Batas API tercapai. Menunggu 60 detik...',
                    'data' => ['id' => $queue->id]
                ];
            }

            // Gagal memproses antrean.
            $queue->update(['status' => 'failed']);
            AIQueueLog::create([
                'queue_id' => $queue->id,
                'attempt' => 1,
                'ai_response' => $e->getMessage(),
                'status' => 'failed',
                'processing_time' => $processingTime,
            ]);

            return [
                'status' => 'failed',
                'data' => [
                    'id' => $queue->id,
                    'queue_status' => 'failed'
                ]
            ];
        }
    }
}
