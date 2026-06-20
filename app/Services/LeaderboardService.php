<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Support\Collection;

/**
 * LeaderboardService
 * ====================
 * Bagian dari tugas Mhs 1: Papan Peringkat / Leaderboard.
 *
 * Strategi ranking:
 * - Mengambil skor TERBAIK per user (bukan menjumlahkan semua attempt),
 *   supaya user yang sering mencoba tidak otomatis menumpuk skor.
 * - Hanya attempt dengan status 'finished' yang dihitung.
 * - Diurutkan dari skor tertinggi, lalu attempt tercepat sebagai tie-breaker.
 */
class LeaderboardService
{
    public function getTopForQuiz(Quiz $quiz, int $limit = 10): Collection
    {
        return QuizAttempt::query()
            ->with('user')
            ->where('quiz_id', $quiz->id)
            ->where('status', 'finished')
            ->selectRaw('user_id, MAX(score) as best_score')
            ->groupBy('user_id')
            ->orderByDesc('best_score')
            ->limit($limit)
            ->get()
            ->map(function ($row) use ($quiz) {
                // Ambil detail attempt terbaik milik user ini untuk info tambahan
                $bestAttempt = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('user_id', $row->user_id)
                    ->where('status', 'finished')
                    ->where('score', $row->best_score)
                    ->orderBy('finished_at')
                    ->first();

                return [
                    'user' => $bestAttempt?->user,
                    'score' => $row->best_score,
                    'correct_count' => $bestAttempt?->correct_count,
                    'wrong_count' => $bestAttempt?->wrong_count,
                    'finished_at' => $bestAttempt?->finished_at,
                ];
            });
    }

    /**
     * Leaderboard global lintas semua quiz (opsional, untuk halaman utama).
     */
    public function getGlobalTop(int $limit = 10): Collection
    {
        return QuizAttempt::query()
            ->with('user')
            ->where('status', 'finished')
            ->selectRaw('user_id, SUM(score) as total_score, COUNT(*) as attempts_count')
            ->groupBy('user_id')
            ->orderByDesc('total_score')
            ->limit($limit)
            ->get();
    }

    public function getUserRank(Quiz $quiz, int $userId): ?int
    {
        $ranked = $this->getTopForQuiz($quiz, 1000);
        $position = $ranked->search(fn ($row) => $row['user']?->id === $userId);

        return $position === false ? null : $position + 1;
    }
}
