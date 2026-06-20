<?php

use App\Http\Controllers\GenerateQuestionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Generate Questions Routes (Mhs 2 - Web Interface)
|--------------------------------------------------------------------------
| Tempel/include blok ini ke dalam routes/web.php,
| di dalam middleware group 'auth' (sama seperti routes Mhs 1).
*/

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/generate-questions', [GenerateQuestionController::class, 'index'])
        ->name('generate-questions');

    Route::post('/generate-questions/store-quiz', [GenerateQuestionController::class, 'storeQuiz'])
        ->name('generate-questions.store-quiz');

    Route::post('/generate-questions/generate', [GenerateQuestionController::class, 'generate'])
        ->name('generate-questions.generate');

    Route::delete('/generate-questions/{quiz}', [GenerateQuestionController::class, 'destroyQuiz'])
        ->name('generate-questions.destroy-quiz');

    Route::get('/generate-questions/{quiz}/preview', [GenerateQuestionController::class, 'preview'])
        ->name('generate-questions.preview');
});
