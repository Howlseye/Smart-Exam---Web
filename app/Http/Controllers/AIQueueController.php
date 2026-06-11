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

    // Tampilkan daftar antrean dengan filter.
    public function index(Request $request)
    {
        $data = $this->aiQueueService->getPaginatedQueues($request);
        $total = $data['total'];
        $queues = $data['queues'];

        return view('queue-ai', compact('queues', 'total'));
    }

    public function create()
    {
        //
    }

    public function store(StoreAIQueueRequest $request)
    {
        //
    }

    public function show(AIQueue $aIQueue)
    {
        //
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

    // Sinkronisasi soal yang belum masuk antrean.
    public function syncMissing()
    {
        $syncedCount = $this->aiQueueService->syncMissingQuestions();

        return back()->with(
            'success', 
            "Pengecekan selesai. Ditemukan {$syncedCount} soal terjawab yang belum masuk antrean, dan kini telah ditambahkan ke AI Queue."
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
