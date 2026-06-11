<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use App\Services\AIQueueService;
use Illuminate\Support\Facades\Log;

class ProcessAIQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(AIQueueService $aiQueueService): void
    {
        // Cek status proses dari Cache.
        $isActive = Cache::get('ai_queue_active', false);
        
        if (!$isActive) {
            // Berhenti jika status tidak aktif.
            Log::info('Proses AI dihentikan pengguna.');
            return;
        }

        // Ambil antrean selanjutnya.
        $queue = $aiQueueService->takeNextQueue();

        if (!$queue) {
            // Berhenti jika semua antrean selesai.
            Cache::put('ai_queue_active', false);
            Log::info('Semua antrean AI selesai.');
            return;
        }

        // Proses skor ke API AI.
        $result = $aiQueueService->processQueue($queue->id);

        // Cetak log ke terminal (queue:work)
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        $statusStr = $result['status'] ?? 'failed';

        if ($statusStr === 'success') {
            $score = $result['data']['score'] ?? 0;
            $output->writeln("  <fg=green;options=bold>✓ SUCCESS</> Soal ID: <fg=cyan>{$queue->id}</> | Skor: <fg=yellow>{$score}</>");
        } elseif ($statusStr === 'rate_limit') {
            $output->writeln("  <fg=yellow;options=bold>⚠ RATE LIMIT</> Soal ID: <fg=cyan>{$queue->id}</> | Menunda 60 detik...");
            Log::warning('Limit API tercapai, menunda 60 detik.');
            self::dispatch()->delay(now()->addSeconds(60));
            return;
        } else {
            $output->writeln("  <fg=red;options=bold>✗ FAILED</> Soal ID: <fg=cyan>{$queue->id}</>");
        }

        // Lanjut proses antrean berikutnya.
        self::dispatch();
    }
}
