<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuestionController;

Route::get('/questions', [QuestionController::class, 'index']);
Route::post('/questions/submit', [QuestionController::class, 'submitAnswers']);
