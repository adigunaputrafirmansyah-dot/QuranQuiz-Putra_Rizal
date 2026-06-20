<?php

use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\QuizGameController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Quiz Engine Routes (Mhs 1)
|--------------------------------------------------------------------------
| Tempel/include blok ini ke dalam routes/web.php,
| di dalam middleware group 'auth' (lihat instruksi di README).
*/

Route::middleware('auth')->group(function () {
    Route::get('/quiz', [QuizGameController::class, 'index'])->name('quiz.index');
    Route::get('/quiz/{quiz}/play', [QuizGameController::class, 'play'])->name('quiz.play');
    Route::post('/quiz/attempt/{attempt}/answer', [QuizGameController::class, 'answer'])->name('quiz.answer');
    Route::get('/quiz/attempt/{attempt}/result', [QuizGameController::class, 'result'])->name('quiz.result');

    Route::get('/quiz/{quiz}/leaderboard', [LeaderboardController::class, 'show'])->name('quiz.leaderboard');
    Route::get('/leaderboard', [LeaderboardController::class, 'global'])->name('leaderboard.global');
});
require __DIR__.'/web-quiz-routes.php';