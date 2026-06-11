<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIQueueController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/queue/take-next', [AIQueueController::class, 'takeNext'])->name('queue.take_next');
Route::post('/queue/process-id/{id}', [AIQueueController::class, 'processId'])->name('queue.process_id');
Route::resource('queue', AIQueueController::class);
Route::post('/queue/sync-missing', [AIQueueController::class, 'syncMissing'])->name('queue.sync_missing');
Route::get('/queue/{queue}/retry', [AIQueueController::class, 'retry'])->name('queue.retry');
