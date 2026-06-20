<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\LeaderboardService;
use App\Services\QuizEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * QuizApiController
 * ===================
 * Versi API (JSON) dari Engine Game Kuis, untuk konsumsi
 * mobile app / SPA frontend nanti. Logic sama persis dengan
 * QuizGameController, hanya beda format response.
 *
 * Semua endpoint ini perlu auth:sanctum (lihat routes/api.php).
 */
class QuizApiController extends Controller
{
    public function __construct(
        private QuizEngineService $engine,
        private LeaderboardService $leaderboard
    ) {
    }

    public function index()
    {
        $quizzes = Quiz::where('is_active', true)->withCount('questions')->get();

        return response()->json(['data' => $quizzes]);
    }

    public function start(Quiz $quiz)
    {
        $quiz->load('questions.options');

        if ($quiz->questions->isEmpty()) {
            return response()->json([
                'message' => 'Soal untuk kuis ini belum tersedia.',
            ], 422);
        }

        $attempt = $this->engine->startAttempt(Auth::id(), $quiz);

        $answeredQuestionIds = $attempt->answers()->pluck('question_id');
        $nextQuestion = $quiz->questions->whereNotIn('id', $answeredQuestionIds)->first();

        if (!$nextQuestion || $attempt->isExpired()) {
            $this->engine->finishAttempt($attempt, $attempt->isExpired() ? 'expired' : 'finished');

            return response()->json([
                'status' => 'finished',
                'attempt_id' => $attempt->id,
            ]);
        }

        return response()->json([
            'attempt_id' => $attempt->id,
            'remaining_seconds' => $attempt->remainingSeconds(),
            'seconds_per_question' => $quiz->seconds_per_question,
            'question_number' => $attempt->answers()->count() + 1,
            'total_questions' => $quiz->questions->count(),
            'question' => [
                'id' => $nextQuestion->id,
                'surah_name' => $nextQuestion->surah_name,
                'ayat_number' => $nextQuestion->ayat_number,
                'ayat_text_arab' => $nextQuestion->ayat_text_arab,
                'ayat_text_translation' => $nextQuestion->ayat_text_translation,
                'question_text' => $nextQuestion->question_text,
                'options' => $nextQuestion->options->map(fn ($o) => [
                    'id' => $o->id,
                    'label' => $o->option_label,
                    'text' => $o->option_text,
                ]),
            ],
        ]);
    }

    public function answer(Request $request, QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === Auth::id(), 403);

        $validOptionIds = $attempt->quiz->questions
            ->flatMap(fn ($q) => $q->options->pluck('id'))
            ->all();

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'option_id' => ['nullable', 'integer', Rule::in($validOptionIds)],
            'answer_time_seconds' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $answer = $this->engine->submitAnswer(
                $attempt,
                $data['question_id'],
                $data['option_id'] ?? null,
                $attempt->quiz->seconds_per_question,
                $data['answer_time_seconds'] ?? null
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $isComplete = $this->engine->isAttemptComplete($attempt);
        if ($isComplete) {
            $this->engine->finishAttempt($attempt, 'finished');
        }

        return response()->json([
            'is_correct' => $answer->is_correct,
            'points_earned' => $answer->points_earned,
            'current_score' => $attempt->fresh()->score,
            'is_complete' => $isComplete,
        ]);
    }

    public function result(QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === Auth::id(), 403);

        $attempt->load(['quiz', 'answers.question', 'answers.option']);

        return response()->json(['data' => $attempt]);
    }

    public function leaderboard(Quiz $quiz)
    {
        return response()->json([
            'data' => $this->leaderboard->getTopForQuiz($quiz, 20),
        ]);
    }
}
