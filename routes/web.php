<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AIQueueController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/queue/start', [AIQueueController::class, 'startProcess'])->name('queue.start');
Route::post('/queue/stop', [AIQueueController::class, 'stopProcess'])->name('queue.stop');
Route::get('/queue/status', [AIQueueController::class, 'statusQueue'])->name('queue.status');
Route::resource('queue', AIQueueController::class);
Route::post('/queue/sync-missing', [AIQueueController::class, 'syncMissing'])->name('queue.sync_missing');
Route::get('/queue/{queue}/retry', [AIQueueController::class, 'retry'])->name('queue.retry');
