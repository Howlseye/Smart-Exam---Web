<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use App\Models\AIQueue;
use App\Models\Question;
use App\Models\AIQueueLog;
use App\Services\AIQueueService;
use App\Jobs\ProcessAIQueueJob;

class AIQueueAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected AIQueueService $aiQueueService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiQueueService = app(AIQueueService::class);
    }

    /**
     * TC-01: Menguji respons API sukses dengan format JSON valid.
     */
    public function test_tc_01_api_response_success()
    {
        $queue = AIQueue::create([
            'question' => 'Apa itu Laravel?',
            'answer' => 'Framework PHP',
            'status' => 'processing'
        ]);

        Question::create([
            'question' => 'Apa itu Laravel?',
            'correct_answer' => 'Framework PHP untuk web artisan',
            'type' => 2
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"score": 85, "confidence": "tinggi"}']]
                ]
            ], 200)
        ]);

        $result = $this->aiQueueService->processQueue($queue->id);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals(85, $result['data']['score']);
        
        $queue->refresh();
        $this->assertEquals('completed', $queue->status);
        $this->assertDatabaseHas('a_i_queue_logs', [
            'queue_id' => $queue->id,
            'score' => 85,
        ]);
    }

    /**
     * TC-02: Menguji penanganan respons saat terkena Rate Limit.
     */
    public function test_tc_02_rate_limit()
    {
        $queue = AIQueue::create([
            'question' => 'Rate limit test',
            'answer' => 'Test',
            'status' => 'processing'
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response('429 Too Many Requests', 429)
        ]);

        $result = $this->aiQueueService->processQueue($queue->id);

        $this->assertEquals('rate_limit', $result['status']);
        
        $queue->refresh();
        $this->assertEquals('pending', $queue->status); // TC-08 related check
    }

    /**
     * TC-03: Menguji penanganan sistem saat server mati / timeout.
     */
    public function test_tc_03_server_error()
    {
        $queue = AIQueue::create([
            'question' => 'Server error test',
            'answer' => 'Test',
            'status' => 'processing'
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response('Internal Server Error', 500)
        ]);

        $result = $this->aiQueueService->processQueue($queue->id);

        $this->assertEquals('failed', $result['status']);
        
        $queue->refresh();
        $this->assertEquals('failed', $queue->status);
    }

    /**
     * TC-04: Menguji penanganan batas bawah skor (nilai 0) dari AI.
     */
    public function test_tc_04_boundary_value_zero()
    {
        $queue = AIQueue::create([
            'question' => 'Zero score test',
            'answer' => 'Test',
            'status' => 'processing'
        ]);

        Question::create([
            'question' => 'Zero score test',
            'correct_answer' => 'Benar',
            'type' => 2
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"score": 0, "confidence": "tinggi"}']]
                ]
            ], 200)
        ]);

        $result = $this->aiQueueService->processQueue($queue->id);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals(0, $result['data']['score']);
        $this->assertDatabaseHas('a_i_queue_logs', [
            'queue_id' => $queue->id,
            'score' => 0,
        ]);
    }

    /**
     * TC-05: Menguji transisi wajar status dari pending ke processing.
     */
    public function test_tc_05_transition_pending_to_processing()
    {
        $queue = AIQueue::create([
            'question' => 'Test transition',
            'answer' => 'Test',
            'status' => 'pending'
        ]);

        $takenQueue = $this->aiQueueService->takeNextQueue();

        $this->assertNotNull($takenQueue);
        $this->assertEquals($queue->id, $takenQueue->id);
        
        $takenQueue->refresh();
        $this->assertEquals('processing', $takenQueue->status);
    }

    /**
     * TC-06: Menguji transisi selesai status dari processing ke completed.
     * Sudah divalidasi oleh TC-01 secara tidak langsung, namun kita pisahkan untuk kejelasan.
     */
    public function test_tc_06_transition_processing_to_completed()
    {
        $this->test_tc_01_api_response_success();
    }

    /**
     * TC-07: Menguji transisi gagal status dari processing ke failed.
     * Sudah divalidasi oleh TC-03 secara tidak langsung.
     */
    public function test_tc_07_transition_processing_to_failed()
    {
        $this->test_tc_03_server_error();
    }

    /**
     * TC-08: Menguji transisi putar balik status dari processing ke pending.
     * Sudah divalidasi oleh TC-02 secara tidak langsung.
     */
    public function test_tc_08_transition_processing_to_pending()
    {
        $this->test_tc_02_rate_limit();
    }

    /**
     * TC-09: Menguji sinkronisasi penarikan esai baru ke dalam antrean.
     */
    public function test_tc_09_sync_new_questions()
    {
        // Model Question memiliki boot event yang membuat AIQueue secara otomatis saat disimpan
        Question::create([
            'question' => 'Soal Sinkronisasi',
            'correct_answer' => 'Jawaban Benar',
            'student_answer' => 'Jawaban Siswa',
            'type' => 2
        ]);

        // Cek apakah otomatis masuk AIQueue dengan status pending
        $this->assertDatabaseHas('a_i_queues', [
            'question' => 'Soal Sinkronisasi',
            'status' => 'pending'
        ]);
    }

    /**
     * TC-10: Menguji mekanisme penundaan Job Worker saat terkena limit.
     */
    public function test_tc_10_delay_job_on_rate_limit()
    {
        Queue::fake();
        Cache::put('ai_queue_active', true);

        $queue = AIQueue::create([
            'question' => 'Delay job test',
            'answer' => 'Test',
            'status' => 'pending'
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response('429 Too Many Requests', 429)
        ]);

        // Jalankan Job secara manual satu kali (dispatchSync)
        $job = new ProcessAIQueueJob();
        $job->handle($this->aiQueueService);

        // Pastikan Job dipanggil kembali dengan delay
        Queue::assertPushed(ProcessAIQueueJob::class, function ($job) {
            return !is_null($job->delay);
        });
    }
}
