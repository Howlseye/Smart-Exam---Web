<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ai:sync-essays')]
#[Description('Sync answered essay questions to AIQueue if they do not have a log')]
class SyncMissedAIQueue extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sync for answered essay questions...');
        
        $essays = \App\Models\Question::where('type', 2)
                    ->whereNotNull('student_answer')
                    ->where('is_answered', true)
                    ->get();
                    
        $syncedCount = 0;
        
        foreach ($essays as $essay) {
            // Cek apakah pertanyaan ini sudah masuk di AIQueue
            // Berhubung AIQueue tidak ada question_id, kita cek berdasarkan teks pertanyaannya.
            // Bisa dicek dari Queue, atau Queue Log. Tapi yang paling aman, cek apakah queue ini 
            // sudah ada di AIQueue sama sekali.
            
            $existingQueue = \App\Models\AIQueue::where('question', $essay->question)->first();
            
            // Jika ada Queue, cek apakah punya log (yang selesai).
            // Tapi perintah user: "masuk ke AIQueue jika di AIQueueLog belum ada sama sekali"
            // Jika tidak ada di Queue, otomatis tidak ada di Log.
            
            $hasLog = false;
            if ($existingQueue) {
                $hasLog = \App\Models\AIQueueLog::where('queue_id', $existingQueue->id)->exists();
            }
            
            // Jika belum ada log sama sekali (atau belum masuk antrean sama sekali)
            if (!$existingQueue || (!$hasLog && $existingQueue->status === 'failed')) {
                // Untuk menghindari duplikasi pending, cek apa ada pending
                $isPending = $existingQueue && $existingQueue->status === 'pending';
                
                if (!$isPending) {
                    \App\Models\AIQueue::create([
                        'question' => $essay->question,
                        'answer' => $essay->student_answer,
                        'status' => 'pending'
                    ]);
                    $syncedCount++;
                }
            }
        }
        
        $this->info("Sync complete. {$syncedCount} essay(s) added to the AI Queue.");
    }
}
