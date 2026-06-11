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

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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

        $total = $query->count();
        $queues = $query->with('logs')->oldest()->paginate(10)->withQueryString();

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
    public function destroy(AIQueue $queue)
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
        // Panggil layanan sync missing.
        $syncedCount = $this->aiQueueService->syncMissingQuestions();

        return back()->with(
            'success', 
            "Pengecekan selesai. Ditemukan {$syncedCount} soal terjawab yang belum masuk antrean, dan kini telah ditambahkan ke AI Queue."
        );
    }

    /**
     * Memulai proses antrean di background.
     */
    public function startProcess()
    {
        // Set status aktif.
        \Illuminate\Support\Facades\Cache::put('ai_queue_active', true);
        
        // Dispatch job ke antrean.
        \App\Jobs\ProcessAIQueueJob::dispatch();

        return response()->json([
            'status' => 'success',
            'message' => 'Proses AI dimulai di latar belakang.'
        ]);
    }

    /**
     * Menghentikan proses antrean.
     */
    public function stopProcess()
    {
        // Set status tidak aktif.
        \Illuminate\Support\Facades\Cache::put('ai_queue_active', false);

        return response()->json([
            'status' => 'success',
            'message' => 'Proses AI dihentikan.'
        ]);
    }

    /**
     * Mengambil status antrean terbaru.
     */
    public function statusQueue()
    {
        $isActive = \Illuminate\Support\Facades\Cache::get('ai_queue_active', false);
        
        // Ambil data antrean terbaru untuk update UI (opsional, bisa sekadar html).
        // Kita cukup mengirim sinyal ke frontend untuk reload.
        $hasProcessing = AIQueue::where('status', 'processing')->exists();
        $hasPending = AIQueue::where('status', 'pending')->exists();

        // Jika tidak ada pending dan proses selesai.
        if (!$hasPending && !$hasProcessing && $isActive) {
            \Illuminate\Support\Facades\Cache::put('ai_queue_active', false);
            $isActive = false;
        }

        return response()->json([
            'is_active' => $isActive,
            'has_processing' => $hasProcessing,
            'has_pending' => $hasPending
        ]);
    }
}
