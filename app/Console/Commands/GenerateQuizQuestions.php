<?php

namespace App\Console\Commands;

use App\Models\Quiz;
use App\Services\QuestionGeneratorService;
use Illuminate\Console\Command;

/**
 * Command: php artisan quiz:generate-questions
 * ================================================
 * Tugas Mhs 2: command manual untuk mengisi soal ke sebuah Quiz
 * dengan menarik ayat acak dari API EQuran.id dan mengubahnya
 * menjadi soal pilihan ganda.
 *
 * Contoh pemakaian:
 *
 *   php artisan quiz:generate-questions
 *       -> akan menanyakan quiz mana & berapa soal secara interaktif
 *
 *   php artisan quiz:generate-questions --quiz=1 --count=10
 *       -> generate 10 soal langsung untuk quiz id=1
 *
 *   php artisan quiz:generate-questions --quiz=1 --count=5 --fresh
 *       -> hapus semua soal lama quiz tersebut dahulu, baru generate yang baru
 */
class GenerateQuizQuestions extends Command
{
    protected $signature = 'quiz:generate-questions
        {--quiz= : ID quiz yang akan diisi soal}
        {--count=10 : Jumlah soal yang ingin di-generate}
        {--fresh : Hapus semua soal lama pada quiz ini sebelum generate}';

    protected $description = 'Generate soal pilihan ganda dari ayat acak API EQuran.id untuk sebuah quiz';

    public function handle(QuestionGeneratorService $generator): int
    {
        $quiz = $this->resolveQuiz();

        if (!$quiz) {
            $this->error('Quiz tidak ditemukan. Proses dibatalkan.');
            return self::FAILURE;
        }

        $count = (int) $this->option('count');

        if ($count < 1 || $count > 100) {
            $this->error('Jumlah soal harus antara 1-100.');
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn("Menghapus {$quiz->questions()->count()} soal lama dari quiz '{$quiz->title}'...");
            foreach ($quiz->questions as $oldQuestion) {
                $oldQuestion->options()->delete();
            }
            $quiz->questions()->delete();
        }

        $this->info("Mengambil {$count} ayat acak dari EQuran.id API dan membuat soal untuk quiz '{$quiz->title}'...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $createdCount = 0;
        $failedCount = 0;

        // Generate satu per satu (bukan sekaligus) supaya progress bar terlihat
        // dan jika satu gagal (misal API timeout), soal lain tetap lanjut dibuat.
        for ($i = 0; $i < $count; $i++) {
            try {
                $generator->generateForQuiz($quiz, 1);
                $createdCount++;
            } catch (\Throwable $e) {
                $failedCount++;
                $this->newLine();
                $this->warn("Gagal membuat 1 soal: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Sinkronkan total_questions & duration_seconds pada quiz
        // agar konsisten dengan jumlah soal yang sebenarnya ada.
        $totalQuestions = $quiz->questions()->count();
        $quiz->update([
            'total_questions' => $totalQuestions,
            'duration_seconds' => $totalQuestions * $quiz->seconds_per_question,
        ]);

        $this->info("Selesai! {$createdCount} soal berhasil dibuat, {$failedCount} gagal.");
        $this->info("Total soal pada quiz '{$quiz->title}' sekarang: {$totalQuestions}.");

        return self::SUCCESS;
    }

    /**
     * Tentukan quiz mana yang dipakai: dari opsi --quiz, atau tanya interaktif
     * kalau opsi tidak diberikan / quiz belum ada sama sekali.
     */
    private function resolveQuiz(): ?Quiz
    {
        $quizId = $this->option('quiz');

        if ($quizId) {
            return Quiz::find($quizId);
        }

        $quizzes = Quiz::all(['id', 'title']);

        if ($quizzes->isEmpty()) {
            $this->warn('Belum ada quiz sama sekali. Membuat quiz baru...');
            $title = $this->ask('Judul quiz baru', 'QuranQuiz - Tebak Ayat & Tafsir');

            return Quiz::create([
                'title' => $title,
                'total_questions' => 0,
                'duration_seconds' => 0,
                'seconds_per_question' => 30,
                'is_active' => true,
            ]);
        }

        $choice = $this->choice(
            'Pilih quiz yang ingin diisi soal',
            $quizzes->map(fn ($q) => "[{$q->id}] {$q->title}")->all()
        );

        $selectedId = (int) trim(explode(']', trim($choice, '[]'))[0]);

        return $quizzes->firstWhere('id', $selectedId) ? Quiz::find($selectedId) : null;
    }
}
