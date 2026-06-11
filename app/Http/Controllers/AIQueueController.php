<?php

namespace App\Http\Controllers;

use App\Models\AIQueue;
use App\Http\Requests\StoreAIQueueRequest;
use App\Http\Requests\UpdateAIQueueRequest;

class AIQueueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $query = AIQueue::query();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
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

        $total = $query->count();
        $queues = $query->with('logs')->latest()->paginate(10)->withQueryString();

        return view('queue-ai', compact('queues', 'total'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAIQueueRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AIQueue $aIQueue)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AIQueue $aIQueue)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAIQueueRequest $request, AIQueue $aIQueue)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AIQueue $queue) // Changed from $aIQueue to $queue to match standard resource binding
    {
        //
    }

    /**
     * Retry the specified failed queue.
     */
    public function retry(AIQueue $queue)
    {
        $queue->update(['status' => 'pending']);
        return back()->with('success', 'Queue status reset to pending for retry.');
    }

    /**
     * Sync answered questions that haven't entered the AI queue yet.
     */
    public function syncMissing()
    {
        // Cari soal essay (type 2) yang sudah terjawab
        $questions = \App\Models\Question::where('type', 2)
            ->whereNotNull('student_answer')
            ->where('student_answer', '!=', '')
            ->get();

        $syncedCount = 0;

        foreach ($questions as $question) {
            $existingQueue = \App\Models\AIQueue::where('question', $question->question)->first();
            
            $hasLog = false;
            if ($existingQueue) {
                $hasLog = \App\Models\AIQueueLog::where('queue_id', $existingQueue->id)->exists();
            }
            
            // Cek kondisi: belum ada antrean sama sekali ATAU antrean gagal dan belum punya log berhasil
            if (!$existingQueue || (!$hasLog && $existingQueue->status === 'failed')) {
                $isPending = $existingQueue && $existingQueue->status === 'pending';
                
                if (!$isPending) {
                    \App\Models\AIQueue::create([
                        'question' => $question->question,
                        'answer' => $question->student_answer,
                        'status' => 'pending'
                    ]);
                    $syncedCount++;
                }
            }
        }

        return back()->with('success', "Pengecekan selesai. Ditemukan {$syncedCount} soal terjawab yang belum masuk antrean, dan kini telah ditambahkan ke AI Queue.");
    }

    public function takeNext()
    {
        $queue = \App\Models\AIQueue::where('status', 'pending')->first();

        if (!$queue) {
            return response()->json(['status' => 'done', 'message' => 'Semua antrean telah selesai.']);
        }

        // Tandai sebagai On Progress
        $queue->update(['status' => 'processing']);

        return response()->json([
            'status' => 'success',
            'data' => ['id' => $queue->id]
        ]);
    }

    public function processId(\Illuminate\Http\Request $request, $id)
    {
        $queue = \App\Models\AIQueue::find($id);
        
        if (!$queue || $queue->status !== 'processing') {
            return response()->json(['status' => 'error', 'message' => 'Antrean tidak valid.']);
        }

        // $apiKey = config('services.gemini.api_key');
        $apiKey = config('services.groq.api_key');
        $prompt = "Tolong berikan nilai untuk jawaban esai berikut.\n\n" .
                  "Pertanyaan: {$queue->question}\n\n" .
                  "Jawaban: {$queue->answer}\n\n" .
                  "Berikan respons dalam format JSON dengan struktur: {\"score\": [angka 0-100], \"confidence\": \"[rendah/sedang/tinggi]\"}. Jangan tambahkan teks lain selain JSON.";

        $startTime = microtime(true);
        try {
            // Gemini API (commented out)
            // $response = \Illuminate\Support\Facades\Http::timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
            //     'contents' => [
            //         [
            //             'parts' => [
            //                 ['text' => $prompt]
            //             ]
            //         ]
            //     ],
            //     'generationConfig' => [
            //         'responseMimeType' => 'application/json'
            //     ]
            // ]);

            // Groq API (OpenAI-compatible)
            $response = \Illuminate\Support\Facades\Http::timeout(60)
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
                // Groq response format (OpenAI-compatible)
                $aiResponseText = $responseData['choices'][0]['message']['content'] ?? '{}';
                
                // Bersihkan respons text dari markdown JSON
                $aiResponseText = preg_replace('/```json\s*(.*?)\s*```/s', '$1', $aiResponseText);
                $parsedResponse = json_decode($aiResponseText, true);

                $score = $parsedResponse['score'] ?? 0;
                $confidence = $parsedResponse['confidence'] ?? 'rendah';

                $log = \App\Models\AIQueueLog::create([
                    'queue_id' => $queue->id,
                    'attempt' => 1,
                    'confidence' => $confidence,
                    'score' => $score,
                    'ai_response' => $aiResponseText,
                    'status' => 'completed',
                    'processing_time' => $processingTime,
                ]);

                $queue->update(['status' => 'completed']);

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'id' => $queue->id,
                        'score' => $score,
                        'ai_response' => \Illuminate\Support\Str::limit($aiResponseText, 50),
                        'processing_time' => $processingTime,
                        'queue_status' => 'completed'
                    ]
                ]);
            } else {
                throw new \Exception("API Error: " . $response->body());
            }
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $processingTime = round($endTime - $startTime);

            if (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'quota')) {
                // Rate limit tercapai, kembalikan status pending agar diproses ulang nanti
                $queue->update(['status' => 'pending']);
                return response()->json([
                    'status' => 'rate_limit', 
                    'message' => 'Batas API tercapai. Menunggu 60 detik...',
                    'data' => ['id' => $queue->id]
                ]);
            }

            $queue->update(['status' => 'failed']);
            \App\Models\AIQueueLog::create([
                'queue_id' => $queue->id,
                'attempt' => 1,
                'ai_response' => $e->getMessage(),
                'status' => 'failed',
                'processing_time' => $processingTime,
            ]);

            return response()->json([
                'status' => 'failed',
                'data' => [
                    'id' => $queue->id,
                    'queue_status' => 'failed'
                ]
            ]);
        }
    }
}
