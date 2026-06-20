<x-app-quranquiz>

    @if (session('error'))
        <div class="mb-6 text-sm rounded-md px-4 py-3" style="background-color: #F5E6E6; color: var(--maroon); border: 1px solid var(--maroon);">
            {{ session('error') }}
        </div>
    @endif

    <!-- Hero -->
    <div class="text-center mb-12">
        <p class="text-xs uppercase tracking-[0.2em] font-semibold mb-3" style="color: var(--maroon);">
            Tebak Ayat &amp; Tafsir
        </p>
        <h1 class="font-display text-4xl sm:text-5xl leading-tight mb-3" style="color: var(--ink-deep);">
            Asah Pemahaman Al-Qur&rsquo;an-mu
        </h1>
        <p class="text-sm max-w-md mx-auto" style="color: var(--ink-text); opacity: 0.7;">
            Setiap soal diambil acak dari ayat suci — kenali surahnya, lanjutkan ayatnya, atau tebak letaknya.
        </p>
    </div>

    @if ($quizzes->isEmpty())
        <div class="manuscript-frame rounded-sm p-12 text-center" style="background-color: white;">
            <p class="font-display text-lg" style="color: var(--ink-deep);">Belum ada kuis tersedia</p>
            <p class="text-sm mt-2" style="color: var(--ink-text); opacity: 0.6;">Silakan kembali lagi nanti.</p>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2">
            @foreach ($quizzes as $quiz)
                <div class="relative overflow-hidden rounded-sm p-6" style="background-color: white; border: 1px solid var(--parchment-border); border-top: 3px solid var(--gold-leaf);">

                    <!-- Motif geometris pojok -->
                    <svg class="islamic-motif-corner top-0 right-0" viewBox="0 0 96 96">
                        <path d="M48 8 L72 32 L48 56 L24 32 Z M48 48 L72 72 L48 96 L24 72 Z" fill="none" stroke="#0B3D2E" stroke-width="2"/>
                    </svg>

                    <h3 class="font-display text-xl mb-3 relative" style="color: var(--ink-deep);">{{ $quiz->title }}</h3>

                    <dl class="text-sm space-y-1.5 mb-6 relative" style="color: var(--ink-text); opacity: 0.75;">
                        <div class="flex justify-between"><dt>Jumlah soal</dt><dd class="font-semibold">{{ $quiz->questions_count }}</dd></div>
                        <div class="flex justify-between"><dt>Waktu per soal</dt><dd class="font-semibold">{{ $quiz->seconds_per_question }} detik</dd></div>
                        <div class="flex justify-between"><dt>Total durasi</dt><dd class="font-semibold">{{ floor($quiz->duration_seconds / 60) }} menit</dd></div>
                    </dl>

                    <div class="flex gap-2 relative">
                        <a href="{{ route('quiz.play', $quiz) }}"
                           class="flex-1 text-center rounded-md px-4 py-2.5 text-sm font-semibold transition-opacity hover:opacity-90"
                           style="background-color: var(--ink-deep); color: var(--parchment);">
                            Mulai Kuis
                        </a>
                        <a href="{{ route('quiz.leaderboard', $quiz) }}"
                           class="rounded-md px-4 py-2.5 text-sm font-semibold transition-colors hover:bg-amber-50"
                           style="border: 1px solid var(--gold-leaf); color: var(--maroon);">
                            Peringkat
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-quranquiz>
