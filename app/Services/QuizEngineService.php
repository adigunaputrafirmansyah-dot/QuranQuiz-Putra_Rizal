<?php

namespace App\Services;

use App\Models\Option;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Carbon\Carbon;

/**
 * QuizEngineService
 * ===================
 * Ini adalah "Engine Game Kuis" sesuai tugas Mhs 1:
 * - Memulai sesi kuis (timer mulai dihitung server-side)
 * - Memvalidasi & menyimpan jawaban + menghitung skor
 * - Sistem skor: skor dasar dari `points` soal, ditambah BONUS
 *   kecepatan jawab (semakin cepat jawab, semakin besar bonus).
 * - Mengakhiri sesi kuis (manual submit atau auto karena waktu habis)
 *
 * Catatan integrasi dengan Mhs 2:
 * Service ini TIDAK peduli dari mana soal berasal (API EQuran.id
 * atau seeder dummy) -- service hanya bekerja dengan data
 * yang sudah ada di tabel `questions` & `options`.
 * Jadi begitu Mhs 2 selesai mengisi data lewat API, engine ini
 * otomatis bisa langsung dipakai tanpa perubahan.
 */
class QuizEngineService
{
    /**
     * Bonus maksimum poin jika user menjawab secepat mungkin (detik ke-0).
     * Bonus berkurang linear sampai 0 saat mendekati batas waktu per soal.
     */
    private const MAX_SPEED_BONUS = 5;

    public function startAttempt(int $userId, Quiz $quiz): QuizAttempt
    {
        // Jika user masih punya attempt yang in_progress untuk quiz ini, lanjutkan saja
        $existing = QuizAttempt::where('user_id', $userId)
            ->where('quiz_id', $quiz->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existing && !$existing->isExpired()) {
            return $existing;
        }

        if ($existing && $existing->isExpired()) {
            $this->finishAttempt($existing, 'expired');
        }

        return QuizAttempt::create([
            'user_id' => $userId,
            'quiz_id' => $quiz->id,
            'started_at' => Carbon::now(),
            'status' => 'in_progress',
        ]);
    }

    /**
     * Submit satu jawaban untuk satu soal dalam attempt yang berjalan.
     *
     * @param QuizAttempt $attempt
     * @param int $questionId
     * @param int|null $optionId  null jika user tidak menjawab (timeout soal)
     * @param int $questionTimeLimitSeconds waktu maksimum per soal (dari quiz->seconds_per_question)
     * @param int|null $clientAnswerTimeSeconds waktu yg dilaporkan klien (opsional, hanya untuk display)
     */
    public function submitAnswer(
        QuizAttempt $attempt,
        int $questionId,
        ?int $optionId,
        int $questionTimeLimitSeconds,
        ?int $clientAnswerTimeSeconds = null
    ): QuizAnswer {
        if ($attempt->status !== 'in_progress') {
            throw new \RuntimeException('Attempt sudah selesai, tidak bisa menjawab lagi.');
        }

        if ($attempt->isExpired()) {
            $this->finishAttempt($attempt, 'expired');
            throw new \RuntimeException('Waktu kuis sudah habis.');
        }

        // Cegah double-submit untuk soal yang sama
        $already = QuizAnswer::where('quiz_attempt_id', $attempt->id)
            ->where('question_id', $questionId)
            ->first();

        if ($already) {
            return $already;
        }

        $question = $attempt->quiz->questions->firstWhere('id', $questionId);
        $option = $optionId ? Option::find($optionId) : null;

        $isCorrect = $option ? (bool) $option->is_correct : false;

        $points = 0;
        if ($isCorrect && $question) {
            $points = $question->points + $this->calculateSpeedBonus(
                $clientAnswerTimeSeconds,
                $questionTimeLimitSeconds
            );
        }

        $answer = QuizAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'question_id' => $questionId,
            'option_id' => $optionId,
            'is_correct' => $isCorrect,
            'points_earned' => $points,
            'answer_time_seconds' => $clientAnswerTimeSeconds,
        ]);

        // Update agregat skor di attempt secara real-time
        $attempt->increment('score', $points);
        $isCorrect ? $attempt->increment('correct_count') : $attempt->increment('wrong_count');

        return $answer;
    }

    /**
     * Bonus kecepatan: linear dari MAX_SPEED_BONUS (jawab di detik 0)
     * turun ke 0 (jawab tepat di batas waktu).
     */
    private function calculateSpeedBonus(?int $answerTime, int $timeLimit): int
    {
        if ($answerTime === null || $timeLimit <= 0) {
            return 0;
        }

        $answerTime = max(0, min($answerTime, $timeLimit));
        $ratio = 1 - ($answerTime / $timeLimit);

        return (int) round(self::MAX_SPEED_BONUS * $ratio);
    }

    public function finishAttempt(QuizAttempt $attempt, string $status = 'finished'): QuizAttempt
    {
        $attempt->update([
            'finished_at' => Carbon::now(),
            'status' => $status,
        ]);

        return $attempt->fresh();
    }

    /**
     * Cek apakah seluruh soal pada quiz sudah dijawab dalam attempt ini.
     */
    public function isAttemptComplete(QuizAttempt $attempt): bool
    {
        $totalQuestions = $attempt->quiz->questions()->count();
        $answeredCount = $attempt->answers()->count();

        return $totalQuestions > 0 && $answeredCount >= $totalQuestions;
    }
}
