<?php

namespace App\Services;

use App\Models\Question;
use App\Models\AIQueue;
use App\Models\AIQueueLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Exception;

class AIQueueService
{
    // Ambil antrean berdasarkan request.
    public function getPaginatedQueues(Request $request)
    {
        $query = AIQueue::query();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('id', 'like', "%{$keyword}%")
                  ->orWhere('question', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return [
            'total' => $query->count(),
            'queues' => $query->with('logs')->oldest()->paginate(10)->withQueryString()
        ];
    }

    // Reset antrean untuk proses ulang.
    public function retryQueue(AIQueue $queue): void
    {
        $queue->update(['status' => 'pending']);
    }

    // Mulai proses AI.
    public function startProcess(): void
    {
        Cache::put('ai_queue_active', true);
        \App\Jobs\ProcessAIQueueJob::dispatch();
    }

    // Hentikan proses AI.
    public function stopProcess(): void
    {
        Cache::put('ai_queue_active', false);
    }

    // Dapatkan status antrean saat ini.
    public function getQueueStatus(): array
    {
        $isActive = Cache::get('ai_queue_active', false);
        
        $hasProcessing = AIQueue::where('status', 'processing')->exists();
        $hasPending = AIQueue::where('status', 'pending')->exists();

        // Matikan jika semua proses selesai.
        if (!$hasPending && !$hasProcessing && $isActive) {
            Cache::put('ai_queue_active', false);
            $isActive = false;
        }

        return [
            'is_active' => $isActive,
            'has_processing' => $hasProcessing,
            'has_pending' => $hasPending
        ];
    }

    // Sinkronisasi soal yang terlewat.
    public function syncMissingQuestions(): int
    {
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
            
            // Evaluasi kondisi masuk antrean.
            if (!$existingQueue || (!$hasLog && $existingQueue->status === 'failed')) {
                $isPending = $existingQueue && $existingQueue->status === 'pending';
                
                if (!$isPending) {
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

    // Ambil antrean pending berikutnya.
    public function takeNextQueue(): ?AIQueue
    {
        $queue = AIQueue::where('status', 'pending')->first();

        if ($queue) {
            $queue->update(['status' => 'processing']);
        }

        return $queue;
    }

    // Proses antrean ke AI.
    public function processQueue(int $id): array
    {
        $queue = AIQueue::find($id);
        
        // Cek validitas antrean.
        if (!$queue || $queue->status !== 'processing') {
            return ['status' => 'error', 'message' => 'Antrean tidak valid.'];
        }

        $apiKey = config('services.groq.api_key');
        
        // Buat prompt permintaan.
        $prompt = "Tolong berikan nilai untuk jawaban esai berikut.\n\n" .
                  "Pertanyaan: {$queue->question}\n\n" .
                  "Jawaban: {$queue->answer}\n\n" .
                  "Berikan respons dalam format JSON dengan struktur: {\"score\": [angka 0-100], \"confidence\": \"[rendah/sedang/tinggi]\"}. Jangan tambahkan teks lain selain JSON.";

        $startTime = microtime(true);
        try {
            // Panggil API LLM.
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
                
                // Hapus blok kode markdown.
                $aiResponseText = preg_replace('/```json\s*(.*?)\s*```/s', '$1', $aiResponseText);
                $parsedResponse = json_decode($aiResponseText, true);

                $score = $parsedResponse['score'] ?? 0;
                $confidence = $parsedResponse['confidence'] ?? 'rendah';

                // Simpan hasil ke log.
                AIQueueLog::create([
                    'queue_id' => $queue->id,
                    'attempt' => 1,
                    'confidence' => $confidence,
                    'score' => $score,
                    'ai_response' => $aiResponseText,
                    'status' => 'completed',
                    'processing_time' => $processingTime,
                ]);

                // Tandai antrean selesai.
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

            // Tangani limit API (429).
            if (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'quota')) {
                $queue->update(['status' => 'pending']);
                return [
                    'status' => 'rate_limit', 
                    'message' => 'Batas API tercapai. Menunggu 60 detik...',
                    'data' => ['id' => $queue->id]
                ];
            }

            // Tandai antrean gagal.
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
