<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\QuizEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class QuizGameController extends Controller
{
    public function __construct(
        private QuizEngineService $engine
    ) {
    }

    /**
     * Halaman daftar quiz yang tersedia & aktif.
     */
    public function index()
    {
        $quizzes = Quiz::where('is_active', true)
            ->withCount('questions')
            ->get();

        return view('quiz.index', compact('quizzes'));
    }

    /**
     * Mulai / lanjutkan attempt kuis, lalu tampilkan halaman game.
     */
    public function play(Quiz $quiz)
    {
        $quiz->load('questions.options');

        if ($quiz->questions->isEmpty()) {
            return back()->with('error', 'Soal untuk kuis ini belum tersedia. Silakan coba lagi nanti.');
        }

        $attempt = $this->engine->startAttempt(Auth::id(), $quiz);

        // Soal yang belum dijawab pada attempt ini
        $answeredQuestionIds = $attempt->answers()->pluck('question_id');
        $nextQuestion = $quiz->questions
            ->whereNotIn('id', $answeredQuestionIds)
            ->first();

        if (!$nextQuestion || $attempt->isExpired()) {
            $this->engine->finishAttempt(
                $attempt,
                $attempt->isExpired() ? 'expired' : 'finished'
            );

            return redirect()->route('quiz.result', $attempt->id);
        }

        return view('quiz.play', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'question' => $nextQuestion,
            'remainingSeconds' => $attempt->remainingSeconds(),
            'questionNumber' => $attempt->answers()->count() + 1,
        ]);
    }

    /**
     * Endpoint submit jawaban (dipanggil via form / fetch dari halaman play).
     */
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
            $this->engine->submitAnswer(
                $attempt,
                $data['question_id'],
                $data['option_id'] ?? null,
                $attempt->quiz->seconds_per_question,
                $data['answer_time_seconds'] ?? null
            );
        } catch (\RuntimeException $e) {
            return redirect()->route('quiz.result', $attempt->id)
                ->with('error', $e->getMessage());
        }

        if ($this->engine->isAttemptComplete($attempt)) {
            $this->engine->finishAttempt($attempt, 'finished');
            return redirect()->route('quiz.result', $attempt->id);
        }

        return redirect()->route('quiz.play', $attempt->quiz_id);
    }

    /**
     * Halaman hasil akhir setelah kuis selesai / waktu habis.
     */
    public function result(QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === Auth::id(), 403);

        $attempt->load(['quiz', 'answers.question', 'answers.option']);

        return view('quiz.result', compact('attempt'));
    }
}
