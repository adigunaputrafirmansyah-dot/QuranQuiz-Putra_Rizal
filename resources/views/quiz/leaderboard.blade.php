<x-app-quranquiz>

    <div class="text-center mb-10 anim-fade-up">
        <p class="text-xs uppercase tracking-[0.2em] font-semibold mb-3" style="color: var(--maroon);">Papan Peringkat</p>
        <h1 class="font-display text-3xl" style="color: var(--ink-deep);">{{ $quiz->title }}</h1>
    </div>

    @if ($myRank)
        <div class="mb-6 text-sm rounded-md px-4 py-3 text-center anim-fade-up" style="background-color: white; border: 1px solid var(--gold-leaf); color: var(--ink-deep); animation-delay: 0.06s;">
            Peringkat kamu saat ini: <span class="font-display text-lg" style="color: var(--maroon);">#{{ $myRank }}</span>
        </div>
    @endif

    <div class="manuscript-frame rounded-sm overflow-hidden anim-fade-up" style="background-color: white; animation-delay: 0.1s;">
        @if ($top->isEmpty())
            <div class="p-12 text-center">
                <p class="font-display text-lg" style="color: var(--ink-deep);">Belum ada yang menyelesaikan kuis ini</p>
                <p class="text-sm mt-2" style="color: var(--ink-text); opacity: 0.6;">Jadilah yang pertama!</p>
            </div>
        @else
            <div class="anim-stagger">
                @foreach ($top as $index => $row)
                    <div class="flex items-center gap-4 px-6 py-4"
                         style="border-bottom: {{ $index < $top->count() - 1 ? '1px solid var(--parchment-border)' : 'none' }}; {{ $index === 0 ? 'background-color: var(--parchment);' : '' }}">
                        <div class="rank-badge {{ $index === 0 ? 'rank-badge-gold' : '' }} w-9 h-9 rounded-full flex items-center justify-center text-sm font-display font-bold"
                             style="background-color: {{ $index === 0 ? 'var(--gold-leaf)' : ($index === 1 ? 'var(--parchment-border)' : ($index === 2 ? 'var(--parchment-dark)' : 'var(--parchment)')) }}; color: {{ $index === 0 ? 'white' : 'var(--ink-deep)' }};">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold" style="color: var(--ink-text);">{{ $row['user']->name ?? 'Pengguna' }}</p>
                            <p class="text-xs mt-0.5" style="color: var(--ink-text); opacity: 0.55;">{{ $row['correct_count'] }} benar &middot; {{ $row['wrong_count'] }} salah</p>
                        </div>
                        <p class="font-display text-lg" style="color: var(--ink-deep);">{{ $row['score'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <a href="{{ route('quiz.index') }}" class="inline-block mt-6 text-sm font-semibold hover:underline" style="color: var(--maroon);">
        ← Kembali ke daftar kuis
    </a>
</x-app-quranquiz>
