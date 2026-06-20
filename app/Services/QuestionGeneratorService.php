<?php

namespace App\Services;

use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use RuntimeException;

/**
 * QuestionGeneratorService
 * ===========================
 * Tugas inti Mhs 2: mengubah ayat acak dari EQuranApiService menjadi
 * 1 baris `Question` + 4 baris `Option` di database lokal, sesuai
 * kontrak struktur tabel yang dibuat Mhs 1.
 *
 * 3 Tipe soal yang didukung (dipilih acak setiap generate):
 *
 * 1. guess_surah        : "Ayat berikut terdapat pada surah apa?"
 *                         Pilihan jawaban = nama-nama surah (1 benar, 3 pengalih).
 *
 * 2. guess_continuation : "Lanjutan dari ayat berikut adalah?"
 *                         Menampilkan ayat ke-N, pilihan jawaban = potongan
 *                         teks ayat ke-(N+1) (1 benar dari surah yang sama,
 *                         3 pengalih dari ayat lain secara acak).
 *
 * 3. guess_ayat_number  : "Ayat ini adalah ayat ke berapa dari surahnya?"
 *                         Pilihan jawaban = angka nomor ayat (1 benar, 3 pengalih
 *                         angka berbeda dalam rentang ayat yang valid).
 *
 * Engine Mhs 1 (QuizEngineService) sama sekali tidak perlu tahu soal
 * datang dari generator ini -- ia hanya membaca tabel `questions`/`options`.
 */
class QuestionGeneratorService
{
    private const QUESTION_TYPES = [
        'guess_surah',
        'guess_continuation',
        'guess_ayat_number',
    ];

    public function __construct(
        private EQuranApiService $api
    ) {
    }

    /**
     * Generate $count soal baru untuk sebuah quiz.
     * Setiap soal dipilih acak dari salah satu dari 3 tipe di atas,
     * lalu disimpan ke tabel `questions` + `options`.
     *
     * @return Question[] soal-soal yang baru dibuat
     */
    public function generateForQuiz(Quiz $quiz, int $count): array
    {
        $created = [];
        $existingOrder = $quiz->questions()->max('order') ?? 0;

        for ($i = 1; $i <= $count; $i++) {
            $type = self::QUESTION_TYPES[array_rand(self::QUESTION_TYPES)];

            $question = match ($type) {
                'guess_surah' => $this->generateGuessSurah($quiz, $existingOrder + $i),
                'guess_continuation' => $this->generateGuessContinuation($quiz, $existingOrder + $i),
                'guess_ayat_number' => $this->generateGuessAyatNumber($quiz, $existingOrder + $i),
            };

            $created[] = $question;
        }

        return $created;
    }

    /**
     * Tipe 1: Tebak nama surah dari ayat yang ditampilkan.
     */
    private function generateGuessSurah(Quiz $quiz, int $order): Question
    {
        $random = $this->api->getRandomAyat();
        $allSurat = $this->api->getAllSurat();

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'surah_number' => $random['suratNomor'],
            'surah_name' => $random['suratNamaLatin'],
            'ayat_number' => $random['ayat']['nomorAyat'],
            'ayat_text_arab' => $random['ayat']['teksArab'],
            'ayat_text_translation' => $random['ayat']['teksIndonesia'],
            'question_text' => 'Ayat di atas terdapat pada surah apa?',
            'question_type' => 'guess_surah',
            'points' => 10,
            'order' => $order,
        ]);

        // Pengalih: 3 nama surah lain, diacak, tidak boleh sama dengan jawaban benar
        $wrongChoices = collect($allSurat)
            ->reject(fn ($s) => (int) $s['nomor'] === $random['suratNomor'])
            ->shuffle()
            ->take(3)
            ->pluck('namaLatin')
            ->all();

        $this->createOptions($question, $random['suratNamaLatin'], $wrongChoices);

        return $question;
    }

    /**
     * Tipe 2: Tebak lanjutan ayat berikutnya dalam surah yang sama.
     * Jika ayat yang diambil adalah ayat terakhir surah, otomatis mundur 1 ayat
     * supaya selalu ada ayat lanjutan yang valid.
     */
    private function generateGuessContinuation(Quiz $quiz, int $order): Question
    {
        $random = $this->api->getRandomAyat();
        $suratNomor = $random['suratNomor'];
        $currentAyatNumber = $random['ayat']['nomorAyat'];
        $jumlahAyat = $random['jumlahAyat'];

        // Pastikan masih ada ayat selanjutnya di surah yang sama
        if ($currentAyatNumber >= $jumlahAyat) {
            $currentAyatNumber = max(1, $jumlahAyat - 1);
            $random['ayat'] = $this->api->getAyat($suratNomor, $currentAyatNumber);
        }

        $nextAyat = $this->api->getAyat($suratNomor, $currentAyatNumber + 1);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'surah_number' => $suratNomor,
            'surah_name' => $random['suratNamaLatin'],
            'ayat_number' => $currentAyatNumber,
            'ayat_text_arab' => $random['ayat']['teksArab'],
            'ayat_text_translation' => $random['ayat']['teksIndonesia'],
            'question_text' => "Lanjutan ayat ke-{$currentAyatNumber} di atas (QS. {$random['suratNamaLatin']}) adalah?",
            'question_type' => 'guess_continuation',
            'points' => 15, // lebih sulit, poin lebih besar
            'order' => $order,
        ]);

        // Pengalih: 3 ayat acak dari surah/ayat lain (translation-nya dipakai sebagai pengalih)
        $wrongChoices = [];
        $attempts = 0;
        while (count($wrongChoices) < 3 && $attempts < 10) {
            $attempts++;
            $decoy = $this->api->getRandomAyat();
            // Hindari kebetulan memilih ayat yang sama dengan jawaban benar
            if ($decoy['suratNomor'] === $suratNomor && $decoy['ayat']['nomorAyat'] === $currentAyatNumber + 1) {
                continue;
            }
            $wrongChoices[] = $decoy['ayat']['teksIndonesia'];
        }

        $this->createOptions($question, $nextAyat['teksIndonesia'], $wrongChoices);

        return $question;
    }

    /**
     * Tipe 3: Tebak nomor urut ayat tersebut dalam surahnya.
     */
    private function generateGuessAyatNumber(Quiz $quiz, int $order): Question
    {
        $random = $this->api->getRandomAyat();
        $correctNumber = $random['ayat']['nomorAyat'];
        $jumlahAyat = $random['jumlahAyat'];

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'surah_number' => $random['suratNomor'],
            'surah_name' => $random['suratNamaLatin'],
            'ayat_number' => $correctNumber,
            'ayat_text_arab' => $random['ayat']['teksArab'],
            'ayat_text_translation' => $random['ayat']['teksIndonesia'],
            'question_text' => "Ayat di atas adalah ayat ke berapa dalam surah {$random['suratNamaLatin']}?",
            'question_type' => 'guess_ayat_number',
            'points' => 10,
            'order' => $order,
        ]);

        // Pengalih: 3 angka berbeda dalam rentang valid ayat surah tersebut
        $wrongNumbers = collect(range(1, max(1, $jumlahAyat)))
            ->reject(fn ($n) => $n === $correctNumber)
            ->shuffle()
            ->take(3)
            ->map(fn ($n) => (string) $n)
            ->all();

        // Fallback jika surah terlalu pendek untuk mendapat 3 pengalih unik (misal Al-Asr, 3 ayat)
        while (count($wrongNumbers) < 3) {
            $candidate = (string) random_int(1, 286); // 286 = jumlah ayat terbanyak (Al-Baqarah)
            if ($candidate !== (string) $correctNumber && !in_array($candidate, $wrongNumbers, true)) {
                $wrongNumbers[] = $candidate;
            }
        }

        $this->createOptions($question, (string) $correctNumber, $wrongNumbers);

        return $question;
    }

    /**
     * Helper umum: buat 4 Option (1 benar + 3 pengalih), urutan diacak,
     * label A/B/C/D ditetapkan sesuai urutan acak tersebut.
     */
    private function createOptions(Question $question, string $correctText, array $wrongTexts): void
    {
        if (count($wrongTexts) < 3) {
            throw new RuntimeException(
                "Tidak cukup pilihan pengalih untuk soal '{$question->question_text}' (butuh 3, dapat " . count($wrongTexts) . ')'
            );
        }

        $allChoices = collect($wrongTexts)
            ->take(3)
            ->map(fn ($text) => ['text' => $text, 'is_correct' => false])
            ->push(['text' => $correctText, 'is_correct' => true])
            ->shuffle()
            ->values();

        $labels = ['A', 'B', 'C', 'D'];

        foreach ($allChoices as $i => $choice) {
            Option::create([
                'question_id' => $question->id,
                'option_label' => $labels[$i],
                'option_text' => $choice['text'],
                'is_correct' => $choice['is_correct'],
            ]);
        }
    }
}
