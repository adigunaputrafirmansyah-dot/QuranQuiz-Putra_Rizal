<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\QuizGameController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('quiz.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/quiz', [QuizGameController::class, 'index'])->name('quiz.index');
    Route::get('/quiz/{quiz}/play', [QuizGameController::class, 'play'])->name('quiz.play');
    Route::post('/quiz/attempt/{attempt}/answer', [QuizGameController::class, 'answer'])->name('quiz.answer');
    Route::get('/quiz/attempt/{attempt}/result', [QuizGameController::class, 'result'])->name('quiz.result');

    Route::get('/quiz/{quiz}/leaderboard', [LeaderboardController::class, 'show'])->name('quiz.leaderboard');
    Route::get('/leaderboard', [LeaderboardController::class, 'global'])->name('leaderboard.global');
});
require __DIR__.'/web-admin-routes.php';
require __DIR__.'/auth.php';