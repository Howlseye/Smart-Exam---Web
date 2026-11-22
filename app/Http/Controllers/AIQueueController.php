<?php

namespace App\Http\Controllers;

use App\Models\AIQueue;
use App\Http\Requests\StoreAIQueueRequest;
use App\Http\Requests\UpdateAIQueueRequest;
use App\Services\AIQueueService;
use Illuminate\Http\Request;

class AIQueueController extends Controller
{
    protected AIQueueService $aiQueueService;

    public function __construct(AIQueueService $aiQueueService)
    {
        $this->aiQueueService = $aiQueueService;
    }

    // Tampilkan daftar antrean dengan filter dan statistik.
    public function index(Request $request)
    {
        $data = $this->aiQueueService->getPaginatedQueues($request);
        $total = $data['total'];
        $queues = $data['queues'];
        $stats = $this->aiQueueService->getQueueStats();

        return view('queue-ai', compact('queues', 'total', 'stats'));
    }

    public function create()
    {
        //
    }

    public function store(StoreAIQueueRequest $request)
    {
        //
    }

    public function show(AIQueue $queue)
    {
        $queue->load('logs');
        $correctAnswer = \App\Models\Question::where('question', $queue->question)->value('correct_answer');
        $latestLog = $queue->logs->last();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $queue->id,
                    'question' => $queue->question,
                    'answer' => $queue->answer,
                    'status' => $queue->status,
                    'created_at' => $queue->created_at ? $queue->created_at->setTimezone('Asia/Jakarta')->format('d M Y H:i:s') . ' WIB' : '-',
                    'updated_at' => $queue->updated_at ? $queue->updated_at->setTimezone('Asia/Jakarta')->format('d M Y H:i:s') . ' WIB' : '-',
                    'correct_answer' => $correctAnswer ?: 'Tidak tersedia kunci jawaban referensi.',
                    'log' => $latestLog ? [
                        'score' => $latestLog->score,
                        'confidence' => $latestLog->confidence,
                        'ai_response' => $latestLog->ai_response,
                        'processing_time' => $latestLog->processing_time,
                        'status' => $latestLog->status,
                        'attempt' => $latestLog->attempt,
                        'created_at' => $latestLog->created_at ? $latestLog->created_at->setTimezone('Asia/Jakarta')->format('d M Y H:i:s') . ' WIB' : '-',
                    ] : null,
                ]
            ]);
        }

        return view('queue-detail', compact('queue', 'correctAnswer', 'latestLog'));
    }

    public function edit(AIQueue $aIQueue)
    {
        //
    }

    public function update(UpdateAIQueueRequest $request, AIQueue $aIQueue)
    {
        //
    }

    public function destroy(AIQueue $queue)
    {
        //
    }

    // Ulangi proses untuk antrean spesifik.
    public function retry(AIQueue $queue)
    {
        $this->aiQueueService->retryQueue($queue);
        
        return back()->with('success', 'Queue status reset to pending for retry.');
    }

    // Ulangi semua antrean yang berstatus failed.
    public function retryAllFailed()
    {
        $count = $this->aiQueueService->retryAllFailed();

        return back()->with(
            $count > 0 ? 'success' : 'info',
            $count > 0 
                ? "Berhasil mereset {$count} antrean gagal kembali ke status pending." 
                : "Tidak ada antrean berstatus gagal yang ditemukan."
        );
    }

    // Ulangi beberapa antrean terpilih sekaligus (bulk retry).
    public function retryBulk(Request $request)
    {
        $ids = $request->input('queue_ids', []);

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'Silakan pilih setidaknya satu antrean untuk diulangi.');
        }

        $count = $this->aiQueueService->retryBulk($ids);

        return back()->with('success', "Berhasil mereset {$count} antrean terpilih kembali ke status pending.");
    }

    // Sinkronisasi soal yang belum masuk antrean.
    public function syncMissing()
    {
        $syncedCount = $this->aiQueueService->syncMissingQuestions();

        return back()->with(
            'success', 
            "Pengecekan selesai. Ditemukan {$syncedCount} soal terjawab yang belum masuk antrean, dan kini telah ditambahkan ke AI Queue."
        );
    }

    // Reset seluruh data simulasi ke kondisi awal
    public function resetSimulation()
    {
        // 1. Bersihkan flag status AI di cache
        \Illuminate\Support\Facades\Cache::forget('ai_queue_active');
        \Illuminate\Support\Facades\Cache::forget('ai_processing');
        \Illuminate\Support\Facades\Cache::forget('ai_processing_stop');

        // 2. Jalankan QuestionSeeder ulang agar data bank soal kembali fresh
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'QuestionSeeder',
            '--force' => true,
        ]);

        // 3. Pastikan 20 soal pertama yang belum dijawab berstatus is_answered = false
        \App\Models\Question::whereNull('student_answer')
            ->orWhere('student_answer', '')
            ->update(['is_answered' => false]);

        // 4. Kosongkan antrean dan log AI agar siap disimulasikan dari awal
        \App\Models\AIQueueLog::query()->delete();
        \App\Models\AIQueue::query()->delete();

        return redirect()->route('queue.index')->with(
            'success', 
            'Simulasi berhasil di-reset ke kondisi awal. Bank soal telah diisi ulang (20 soal siap diambil aplikasi Android) dan antrean AI telah dikosongkan.'
        );
    }

    // Mulai proses background antrean.
    public function startProcess()
    {
        $this->aiQueueService->startProcess();

        return response()->json([
            'status' => 'success',
            'message' => 'Proses AI dimulai di latar belakang.'
        ]);
    }

    // Hentikan proses background antrean.
    public function stopProcess()
    {
        $this->aiQueueService->stopProcess();

        return response()->json([
            'status' => 'success',
            'message' => 'Proses AI dihentikan.'
        ]);
    }

    // Ambil status proses antrean terbaru.
    public function statusQueue()
    {
        $status = $this->aiQueueService->getQueueStatus();

        return response()->json($status);
    }
}
