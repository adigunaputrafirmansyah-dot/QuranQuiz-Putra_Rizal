<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-emerald-900 leading-tight">
            QuranQuiz — Pilih Kuis
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('error'))
                <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-rose-700 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-8">
                <p class="text-sm uppercase tracking-widest text-amber-600 font-semibold mb-1">Tebak Ayat & Tafsir</p>
                <h1 class="text-2xl font-bold text-emerald-950">Asah pemahamanmu terhadap kandungan Al-Qur'an</h1>
            </div>

            @if ($quizzes->isEmpty())
                <div class="rounded-xl border border-dashed border-emerald-200 bg-emerald-50/50 p-10 text-center">
                    <p class="text-emerald-700">Belum ada kuis aktif saat ini. Coba lagi nanti.</p>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($quizzes as $quiz)
                        <div class="relative overflow-hidden rounded-xl border border-emerald-100 bg-white shadow-sm hover:shadow-md transition-shadow">
                            <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-amber-100 to-transparent rounded-bl-full opacity-60"></div>
                            <div class="relative p-6">
                                <h3 class="font-bold text-lg text-emerald-950 mb-2">{{ $quiz->title }}</h3>
                                <dl class="text-sm text-emerald-700/80 space-y-1 mb-5">
                                    <div class="flex justify-between"><dt>Jumlah soal</dt><dd class="font-medium">{{ $quiz->questions_count }}</dd></div>
                                    <div class="flex justify-between"><dt>Waktu per soal</dt><dd class="font-medium">{{ $quiz->seconds_per_question }}s</dd></div>
                                    <div class="flex justify-between"><dt>Total durasi</dt><dd class="font-medium">{{ floor($quiz->duration_seconds / 60) }} menit</dd></div>
                                </dl>
                                <div class="flex gap-2">
                                    <a href="{{ route('quiz.play', $quiz) }}"
                                       class="flex-1 text-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800 transition-colors">
                                        Mulai Kuis
                                    </a>
                                    <a href="{{ route('quiz.leaderboard', $quiz) }}"
                                       class="rounded-lg border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-50 transition-colors">
                                        Peringkat
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
