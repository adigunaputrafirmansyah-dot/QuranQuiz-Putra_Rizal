<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel `quiz_answers`
     * Mencatat jawaban user untuk setiap soal dalam 1 attempt.
     * Dipakai Engine untuk:
     * - validasi jawaban benar/salah
     * - hitung waktu jawab per soal (untuk bonus skor "lebih cepat lebih besar poin")
     */
    public function up(): void
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('options')->nullOnDelete();

            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('points_earned')->default(0);
            $table->unsignedInteger('answer_time_seconds')->nullable(); // berapa detik user menjawab

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
