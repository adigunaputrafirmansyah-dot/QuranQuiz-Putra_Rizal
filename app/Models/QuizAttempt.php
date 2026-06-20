<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'started_at',
        'finished_at',
        'score',
        'correct_count',
        'wrong_count',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    /**
     * Hitung sisa waktu (detik) berdasarkan started_at dan durasi quiz.
     * Ini dipakai Engine untuk validasi timer di server-side
     * (jangan percaya timer dari frontend saja, supaya tidak bisa dicurangi).
     */
    public function remainingSeconds(): int
    {
        if (!$this->started_at) {
            return 0;
        }

        $elapsed = now()->diffInSeconds($this->started_at);
        $remaining = $this->quiz->duration_seconds - $elapsed;

        return max(0, $remaining);
    }

    public function isExpired(): bool
    {
        return $this->remainingSeconds() <= 0;
    }
}
