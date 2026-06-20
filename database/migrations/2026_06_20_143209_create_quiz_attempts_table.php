<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel `quiz_attempts`
     * Bagian INTI Engine Game Kuis (tugas Mhs 1).
     * Setiap kali user memulai kuis, dibuat 1 row attempt.
     * - started_at  : dipakai untuk hitung sisa waktu (timer) di server-side
     * - finished_at : diisi saat user submit / waktu habis
     * - score       : total skor akhir
     * - status      : in_progress | finished | expired
     */
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('wrong_count')->default(0);

            $table->enum('status', ['in_progress', 'finished', 'expired'])
                  ->default('in_progress');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
