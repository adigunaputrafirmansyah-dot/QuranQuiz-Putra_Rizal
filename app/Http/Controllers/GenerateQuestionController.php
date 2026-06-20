<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Services\QuestionGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * GenerateQuestionController
 * =============================
 * Versi WEB dari tugas Mhs 2 (sebelumnya hanya lewat artisan command).
 * Controller ini hanya membungkus QuestionGeneratorService yang sama --
 * tidak ada logic baru, supaya hasil generate dari CLI dan dari web
 * konsisten 100%.
 */
class GenerateQuestionController extends Controller
{
    public function __construct(
        private QuestionGeneratorService $generator
    ) {
    }

    /**
     * Halaman form: pilih quiz, jumlah soal, opsi hapus soal lama.
     */
    public function index()
    {
        $quizzes = Quiz::withCount('questions')->orderBy('id')->get();

        return view('admin.generate-questions', compact('quizzes'));
    }

    /**
     * Buat quiz baru kosong (judul saja), supaya bisa langsung diisi soal.
     */
    public function storeQuiz(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'seconds_per_question' => ['required', 'integer', 'min:5', 'max:300'],
        ]);

        $quiz = Quiz::create([
            'title' => $data['title'],
            'total_questions' => 0,
            'duration_seconds' => 0,
            'seconds_per_question' => $data['seconds_per_question'],
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.generate-questions')
            ->with('success', "Quiz '{$quiz->title}' berhasil dibuat. Sekarang generate soalnya di bawah.");
    }

    /**
     * Proses generate soal untuk quiz yang dipilih.
     */
    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'quiz_id' => ['required', 'exists:quizzes,id'],
            'count' => ['required', 'integer', 'min:1', 'max:50'],
            'fresh' => ['nullable', 'boolean'],
        ]);

        $quiz = Quiz::findOrFail($data['quiz_id']);
        $fresh = (bool) ($data['fresh'] ?? false);

        if ($fresh) {
            foreach ($quiz->questions as $oldQuestion) {
                $oldQuestion->options()->delete();
            }
            $quiz->questions()->delete();
        }

        $createdCount = 0;
        $failedCount = 0;
        $errors = [];

        for ($i = 0; $i < $data['count']; $i++) {
            try {
                $this->generator->generateForQuiz($quiz, 1);
                $createdCount++;
            } catch (\Throwable $e) {
                $failedCount++;
                $errors[] = $e->getMessage();
            }
        }

        $totalQuestions = $quiz->questions()->count();
        $quiz->update([
            'total_questions' => $totalQuestions,
            'duration_seconds' => $totalQuestions * $quiz->seconds_per_question,
        ]);

        $message = "Berhasil membuat {$createdCount} soal baru untuk '{$quiz->title}'. Total soal sekarang: {$totalQuestions}.";

        if ($failedCount > 0) {
            $message .= " ({$failedCount} gagal dibuat, kemungkinan API sedang lambat — coba generate lagi.)";
        }

        return redirect()
            ->route('admin.generate-questions')
            ->with($failedCount > 0 && $createdCount === 0 ? 'error' : 'success', $message)
            ->with('error_details', $errors);
    }

    /**
     * Hapus 1 quiz beserta seluruh soal & opsinya.
     */
    public function destroyQuiz(Quiz $quiz): RedirectResponse
    {
        foreach ($quiz->questions as $question) {
            $question->options()->delete();
        }
        $quiz->questions()->delete();
        $title = $quiz->title;
        $quiz->delete();

        return redirect()
            ->route('admin.generate-questions')
            ->with('success', "Quiz '{$title}' dan semua soalnya berhasil dihapus.");
    }

    /**
     * Lihat preview soal-soal sebuah quiz (untuk verifikasi hasil generate).
     */
    public function preview(Quiz $quiz)
    {
        $quiz->load('questions.options');

        return view('admin.preview-questions', compact('quiz'));
    }
}
