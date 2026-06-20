<?php

use App\Http\Controllers\Api\QuizApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Quiz Engine API Routes (Mhs 1)
|--------------------------------------------------------------------------
| Tempel/include blok ini ke dalam routes/api.php,
| di dalam middleware group 'auth:sanctum'.
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/quizzes', [QuizApiController::class, 'index']);
    Route::post('/quizzes/{quiz}/start', [QuizApiController::class, 'start']);
    Route::post('/quiz-attempts/{attempt}/answer', [QuizApiController::class, 'answer']);
    Route::get('/quiz-attempts/{attempt}/result', [QuizApiController::class, 'result']);
    Route::get('/quizzes/{quiz}/leaderboard', [QuizApiController::class, 'leaderboard']);
});