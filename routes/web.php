<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIQueueController;

Route::get('/', function () {
    return redirect()->route('queue.index');
});

Route::post('/queue/start', [AIQueueController::class, 'startProcess'])->name('queue.start');
Route::post('/queue/stop', [AIQueueController::class, 'stopProcess'])->name('queue.stop');
Route::get('/queue/status', [AIQueueController::class, 'statusQueue'])->name('queue.status');
Route::resource('queue', AIQueueController::class);
Route::post('/queue/sync-missing', [AIQueueController::class, 'syncMissing'])->name('queue.sync_missing');
Route::post('/queue/reset-simulation', [AIQueueController::class, 'resetSimulation'])->name('queue.reset_simulation');
Route::post('/queue/retry-all-failed', [AIQueueController::class, 'retryAllFailed'])->name('queue.retry_all_failed');
Route::post('/queue/retry-bulk', [AIQueueController::class, 'retryBulk'])->name('queue.retry_bulk');
Route::get('/queue/{queue}/retry', [AIQueueController::class, 'retry'])->name('queue.retry');
