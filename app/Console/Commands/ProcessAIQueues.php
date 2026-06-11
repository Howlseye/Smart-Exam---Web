<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\AIQueue;
use App\Models\AIQueueLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

#[Signature('ai:process-queues')]
#[Description('Process pending AI Queues and grade answers via Groq AI')]
class ProcessAIQueues extends Command
{
    public function handle()
    {
        // Tandai bahwa proses sedang berjalan
        \Illuminate\Support\Facades\Cache::put('ai_processing', true, now()->addHours(1));
        // Hapus flag stop jika sebelumnya ada
        \Illuminate\Support\Facades\Cache::forget('ai_processing_stop');

        // $apiKey = config('services.gemini.api_key');
        $apiKey = config('services.groq.api_key');
        $pendingCount = AIQueue::where('status', 'pending')->count();
        $this->info("Found {$pendingCount} pending queues.");

        while ($queue = AIQueue::where('status', 'pending')->first()) {
            // Cek apakah ada instruksi penghentian dari user
            if (\Illuminate\Support\Facades\Cache::get('ai_processing_stop')) {
                $this->info("Menerima sinyal stop dari user. Menghentikan proses...");
                \Illuminate\Support\Facades\Cache::forget('ai_processing_stop');
                break;
            }

            $this->info("Processing queue ID: {$queue->id}");

            $queue->update(['status' => 'processing']);

            $prompt = "Tolong berikan nilai untuk jawaban esai berikut.\n\n" .
                "Pertanyaan: {$queue->question}\n\n" .
                "Jawaban: {$queue->answer}\n\n" .
                "Berikan respons dalam format JSON dengan struktur: {\"score\": [angka 0-100], \"confidence\": \"[rendah/sedang/tinggi]\"}. Jangan tambahkan teks lain selain JSON.";

            $startTime = microtime(true);
            try {
                // $response = Http::timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
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

                $response = Http::timeout(60)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post("https://api.groq.com/openai/v1/chat/completions", [
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

                    // Bersihkan response text agar hanya berisi JSON jika ada format markdown
                    $aiResponseText = preg_replace('/```json\s*(.*?)\s*```/s', '$1', $aiResponseText);

                    $parsedResponse = json_decode($aiResponseText, true);

                    $score = $parsedResponse['score'] ?? 0;
                    $confidence = $parsedResponse['confidence'] ?? 'rendah';

                    AIQueueLog::create([
                        'queue_id' => $queue->id,
                        'attempt' => 1,
                        'confidence' => $confidence,
                        'score' => $score,
                        'ai_response' => $aiResponseText,
                        'status' => 'completed',
                        'processing_time' => $processingTime,
                    ]);

                    $queue->update(['status' => 'completed']);
                    $this->info("Queue ID: {$queue->id} completed with score: {$score} in {$processingTime}s");
                } else {
                    throw new \Exception("API Error: " . $response->body());
                }
            } catch (\Exception $e) {
                $endTime = microtime(true);
                $processingTime = round($endTime - $startTime);

                Log::error("Error processing queue {$queue->id}: " . $e->getMessage());

                // Jika error karena rate limit (429), beri tahu untuk menunggu lebih lama
                if (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'quota')) {
                    $this->warn("Rate limit tercapai pada Queue ID: {$queue->id}. Menunggu 60 detik sebelum melanjutkan...");

                    // Kembalikan status ke pending agar diproses ulang di iterasi berikutnya
                    $queue->update(['status' => 'pending']);

                    sleep(60);
                    continue;
                }

                $queue->update(['status' => 'failed']);

                AIQueueLog::create([
                    'queue_id' => $queue->id,
                    'attempt' => 1,
                    'ai_response' => $e->getMessage(),
                    'status' => 'failed',
                    'processing_time' => $processingTime,
                ]);

                $this->error("Queue ID: {$queue->id} failed in {$processingTime}s.");
            }

            // Jeda 2 detik antar request (Groq allows 30 RPM)
            sleep(2);
        }

        $this->info("Finished processing queues.");

        // Hapus flag karena proses sudah selesai
        \Illuminate\Support\Facades\Cache::forget('ai_processing');
    }
}
