<x-app-quranquiz>

    @if (session('error'))
        <div class="mb-6 text-sm rounded-md px-4 py-3 anim-fade-up" style="background-color: #F5E6E6; color: var(--maroon); border: 1px solid var(--maroon);">
            {{ session('error') }}
        </div>
    @endif

    <!-- Kartu skor utama -->
    <div class="manuscript-frame rounded-sm p-10 text-center mb-8 relative overflow-hidden anim-fade-up" style="background-color: white;">
        <svg class="islamic-motif-corner top-0 left-0" viewBox="0 0 96 96">
            <path d="M48 8 L72 32 L48 56 L24 32 Z M48 48 L72 72 L48 96 L24 72 Z" fill="none" stroke="#C9A24B" stroke-width="2"/>
        </svg>

        <p class="text-xs uppercase tracking-[0.2em] font-semibold mb-2 relative" style="color: var(--maroon);">
            {{ $attempt->quiz->title }}
        </p>
        <p id="score-display" class="font-display text-6xl mb-1 relative" style="color: var(--ink-deep);">0</p>
        <p class="text-sm mb-8 relative" style="color: var(--ink-text); opacity: 0.6;">Total Poin</p>

        <div class="flex justify-center gap-10 text-sm relative">
            <div>
                <p class="font-display text-2xl" style="color: var(--ink-deep);">{{ $attempt->correct_count }}</p>
                <p class="mt-1" style="color: var(--ink-text); opacity: 0.6;">Benar</p>
            </div>
            <div>
                <p class="font-display text-2xl" style="color: var(--maroon);">{{ $attempt->wrong_count }}</p>
                <p class="mt-1" style="color: var(--ink-text); opacity: 0.6;">Salah</p>
            </div>
            <div>
                <p class="font-display text-2xl capitalize" style="color: var(--gold-leaf);">{{ $attempt->status }}</p>
                <p class="mt-1" style="color: var(--ink-text); opacity: 0.6;">Status</p>
            </div>
        </div>
    </div>

    <div class="flex gap-3 mb-10 anim-fade-up" style="animation-delay: 0.1s;">
        <a href="{{ route('quiz.leaderboard', $attempt->quiz_id) }}"
           class="btn-animated flex-1 text-center rounded-md px-4 py-3 text-sm font-semibold"
           style="background-color: var(--ink-deep); color: var(--parchment);">
            Lihat Peringkat
        </a>
        <a href="{{ route('quiz.index') }}"
           class="btn-animated flex-1 text-center rounded-md px-4 py-3 text-sm font-semibold transition-colors hover:bg-amber-50"
           style="border: 1px solid var(--gold-leaf); color: var(--maroon);">
            Kuis Lainnya
        </a>
    </div>

    <h3 class="font-display text-xl mb-4 anim-fade-up" style="color: var(--ink-deep); animation-delay: 0.15s;">Rincian Jawaban</h3>
    <div class="space-y-3 anim-stagger">
        @foreach ($attempt->answers as $answer)
            <div class="rounded-sm px-5 py-4" style="background-color: white; border: 1px solid var(--parchment-border); border-left: 4px solid {{ $answer->is_correct ? 'var(--ink-deep)' : 'var(--maroon)' }};">
                <div class="flex justify-between items-start gap-3">
                    <p class="text-sm flex-1" style="color: var(--ink-text);">{{ $answer->question->question_text }}</p>
                    <span class="text-xs font-bold whitespace-nowrap" style="color: {{ $answer->is_correct ? 'var(--ink-deep)' : 'var(--maroon)' }};">
                        {{ $answer->is_correct ? '+' . $answer->points_earned : '0' }} pts
                    </span>
                </div>
                <p class="text-xs mt-1.5" style="color: var(--ink-text); opacity: 0.6;">
                    Jawabanmu: {{ $answer->option?->option_text ?? '(tidak menjawab)' }}
                </p>
            </div>
        @endforeach
    </div>

    <script>
        (function () {
            const finalScore = {{ $attempt->score }};
            const el = document.getElementById('score-display');
            const duration = 900; // ms
            const start = performance.now();

            function tick(now) {
                const progress = Math.min(1, (now - start) / duration);
                // ease-out cubic, mulai cepat lalu melambat saat mendekati skor akhir
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.round(eased * finalScore);

                if (progress < 1) {
                    requestAnimationFrame(tick);
                }
            }

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                el.textContent = finalScore;
            } else {
                requestAnimationFrame(tick);
            }
        })();
    </script>
</x-app-quranquiz>
