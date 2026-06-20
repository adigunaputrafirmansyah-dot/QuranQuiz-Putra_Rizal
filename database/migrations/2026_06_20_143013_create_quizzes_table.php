<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel `quizzes`
     * Merepresentasikan satu "ronde/sesi" kuis yang bisa diisi banyak soal.
     * Mhs 2 akan mengisi soal-soal (questions) ke dalam quiz ini
     * lewat integrasi API EQuran.id.
     */
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('QuranQuiz');
            $table->unsignedInteger('total_questions')->default(10);
            // durasi total kuis dalam detik, default 10 soal x 30 detik
            $table->unsignedInteger('duration_seconds')->default(300);
            $table->unsignedInteger('seconds_per_question')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
