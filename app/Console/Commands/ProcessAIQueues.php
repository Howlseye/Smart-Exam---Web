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
#[Description('Process pending AI Queues and grade answers via Gemini AI')]
class ProcessAIQueues extends Command
{
    public function handle()
    {
        $apiKey = config('services.gemini.api_key');
        $pendingCount = AIQueue::where('status', 'pending')->count();
        $this->info("Found {$pendingCount} pending queues.");

        while ($queue = AIQueue::where('status', 'pending')->first()) {
            $this->info("Processing queue ID: {$queue->id}");
            
            $queue->update(['status' => 'processing']);

            $prompt = "Tolong berikan nilai untuk jawaban esai berikut.\n\n" .
                      "Pertanyaan: {$queue->question}\n\n" .
                      "Jawaban: {$queue->answer}\n\n" .
                      "Berikan respons dalam format JSON dengan struktur: {\"score\": [angka 0-100], \"confidence\": \"[rendah/sedang/tinggi]\"}. Jangan tambahkan teks lain selain JSON.";

            try {
                $response = Http::timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json'
                    ]
                ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    
                    $aiResponseText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                    
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
                    ]);

                    $queue->update(['status' => 'completed']);
                    $this->info("Queue ID: {$queue->id} completed with score: {$score}");
                } else {
                    throw new \Exception("API Error: " . $response->body());
                }
            } catch (\Exception $e) {
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
                ]);
                
                $this->error("Queue ID: {$queue->id} failed.");
            }
            
            // Jeda 12 detik antar request untuk menghindari limit 5 request per menit
            sleep(12);
        }

        $this->info("Finished processing queues.");
    }
}
