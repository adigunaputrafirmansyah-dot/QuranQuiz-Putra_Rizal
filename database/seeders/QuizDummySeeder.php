<?php

namespace Database\Seeders;

use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

/**
 * QuizDummySeeder
 * =================
 * Seeder ini HANYA untuk keperluan development & testing Engine
 * (sebelum Mhs 2 selesai integrasi API EQuran.id).
 *
 * Struktur data di sini adalah CONTOH/KONTRAK yang harus diikuti
 * Mhs 2 saat mengisi tabel `questions` & `options` dari hasil
 * fetch API EQuran.id:
 *
 * 1 Quiz punya banyak Question.
 * 1 Question punya 4 Option (1 is_correct = true, 3 sisanya false).
 *
 * Cara pakai: php artisan db:seed --class=QuizDummySeeder
 */
class QuizDummySeeder extends Seeder
{
    public function run(): void
    {
        $quiz = Quiz::create([
            'title' => 'QuranQuiz - Surah Pilihan',
            'total_questions' => 5,
            'duration_seconds' => 150, // 5 soal x 30 detik
            'seconds_per_question' => 30,
            'is_active' => true,
        ]);

        $dummyQuestions = [
            [
                'surah_number' => 1,
                'surah_name' => 'Al-Fatihah',
                'ayat_number' => 2,
                'ayat_text_arab' => 'الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ',
                'ayat_text_translation' => 'Segala puji bagi Allah, Tuhan seluruh alam.',
                'question_text' => 'Ayat di atas terdapat pada surah apa?',
                'correct' => 'Al-Fatihah',
                'wrong' => ['Al-Baqarah', 'Al-Ikhlas', 'An-Nas'],
            ],
            [
                'surah_number' => 2,
                'surah_name' => 'Al-Baqarah',
                'ayat_number' => 255,
                'ayat_text_arab' => 'اللَّهُ لَا إِلَهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ',
                'ayat_text_translation' => 'Allah, tidak ada Tuhan selain Dia, Yang Maha Hidup, Yang terus menerus mengurus (makhluk-Nya).',
                'question_text' => 'Ayat di atas dikenal dengan sebutan apa?',
                'correct' => 'Ayat Kursi',
                'wrong' => ['Ayat Sajdah', 'Ayat Kauniyah', 'Ayat Muhkamat'],
            ],
            [
                'surah_number' => 112,
                'surah_name' => 'Al-Ikhlas',
                'ayat_number' => 1,
                'ayat_text_arab' => 'قُلْ هُوَ اللَّهُ أَحَدٌ',
                'ayat_text_translation' => 'Katakanlah (Muhammad), "Dialah Allah, Yang Maha Esa.',
                'question_text' => 'Ayat di atas merupakan ayat pertama dari surah apa?',
                'correct' => 'Al-Ikhlas',
                'wrong' => ['Al-Falaq', 'An-Nas', 'Al-Kafirun'],
            ],
            [
                'surah_number' => 36,
                'surah_name' => 'Yasin',
                'ayat_number' => 1,
                'ayat_text_arab' => 'يس',
                'ayat_text_translation' => 'Ya Sin.',
                'question_text' => 'Surah ke berapakah Yasin dalam urutan Al-Qur\'an?',
                'correct' => 'Surah ke-36',
                'wrong' => ['Surah ke-30', 'Surah ke-40', 'Surah ke-18'],
            ],
            [
                'surah_number' => 114,
                'surah_name' => 'An-Nas',
                'ayat_number' => 1,
                'ayat_text_arab' => 'قُلْ أَعُوذُ بِرَبِّ النَّاسِ',
                'ayat_text_translation' => 'Katakanlah, "Aku berlindung kepada Tuhan manusia.',
                'question_text' => 'Surah An-Nas adalah surah ke berapa dari akhir Al-Qur\'an?',
                'correct' => 'Surah terakhir (ke-114)',
                'wrong' => ['Surah ke-113', 'Surah ke-112', 'Surah ke-1'],
            ],
        ];

        foreach ($dummyQuestions as $index => $item) {
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'surah_number' => $item['surah_number'],
                'surah_name' => $item['surah_name'],
                'ayat_number' => $item['ayat_number'],
                'ayat_text_arab' => $item['ayat_text_arab'],
                'ayat_text_translation' => $item['ayat_text_translation'],
                'question_text' => $item['question_text'],
                'question_type' => 'multiple_choice',
                'points' => 10,
                'order' => $index + 1,
            ]);

            // Gabung jawaban benar + salah, lalu acak urutannya
            $allOptions = collect($item['wrong'])
                ->map(fn ($text) => ['text' => $text, 'is_correct' => false])
                ->push(['text' => $item['correct'], 'is_correct' => true])
                ->shuffle()
                ->values();

            $labels = ['A', 'B', 'C', 'D'];
            foreach ($allOptions as $i => $opt) {
                Option::create([
                    'question_id' => $question->id,
                    'option_label' => $labels[$i],
                    'option_text' => $opt['text'],
                    'is_correct' => $opt['is_correct'],
                ]);
            }
        }

        $this->command->info('QuizDummySeeder: 1 quiz + 5 soal dummy berhasil dibuat.');
    }
}
