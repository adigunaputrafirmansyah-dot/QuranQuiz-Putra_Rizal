<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel `questions`
     * INI ADALAH KONTRAK STRUKTUR DATA UNTUK MHS 2.
     * Mhs 2 akan mengisi tabel ini dari hasil fetch API EQuran.id
     * (ambil ayat acak) lalu membuat 4 pilihan jawaban (1 benar, 3 pengalih).
     *
     * Field `surah_number` & `ayat_number` disimpan agar bisa ditampilkan
     * referensi ayatnya (misal: "QS. Al-Baqarah: 255").
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();

            // Referensi ayat dari EQuran.id API
            $table->unsignedInteger('surah_number')->nullable();
            $table->string('surah_name')->nullable();
            $table->unsignedInteger('ayat_number')->nullable();

            // Teks ayat (arab) dan terjemahan jadi badan soal
            $table->text('ayat_text_arab')->nullable();
            $table->text('ayat_text_translation')->nullable();

            // Pertanyaan yang ditampilkan ke user, contoh:
            // "Lanjutan dari ayat berikut adalah?" / "Ayat ini terdapat pada surah apa?"
            $table->text('question_text');

            // Tipe soal, untuk fleksibilitas Mhs 2 membuat variasi soal
            $table->string('question_type')->default('multiple_choice');

            $table->unsignedInteger('points')->default(10);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
