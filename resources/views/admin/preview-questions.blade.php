<x-app-quranquiz>

    <a href="{{ route('admin.generate-questions') }}" class="inline-block mb-8 text-sm font-semibold hover:underline" style="color: var(--maroon);">
        ← Kembali ke Generate Soal
    </a>

    <div class="text-center mb-10 anim-fade-up">
        <p class="text-xs uppercase tracking-[0.2em] font-semibold mb-3" style="color: var(--maroon);">Preview Soal</p>
        <h1 class="font-display text-3xl" style="color: var(--ink-deep);">{{ $quiz->title }}</h1>
    </div>

    @if ($quiz->questions->isEmpty())
        <div class="manuscript-frame rounded-sm p-12 text-center anim-fade-up" style="background-color: white;">
            <p class="font-display text-lg" style="color: var(--ink-deep);">Belum ada soal pada quiz ini</p>
        </div>
    @else
        <div class="space-y-5 anim-stagger">
            @foreach ($quiz->questions as $index => $question)
                <div class="card-lift rounded-sm p-6" style="background-color: white; border: 1px solid var(--parchment-border); border-left: 4px solid var(--gold-leaf);">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wide" style="color: var(--maroon);">
                            Soal {{ $index + 1 }} &middot; {{ str_replace('_', ' ', $question->question_type) }}
                        </span>
                        <span class="text-xs" style="color: var(--ink-text); opacity: 0.55;">
                            QS. {{ $question->surah_name }} : {{ $question->ayat_number }}
                        </span>
                    </div>

                    @if ($question->ayat_text_arab)
                        <p dir="rtl" class="font-display text-lg text-right mb-2" style="color: var(--ink-deep);">
                            {{ $question->ayat_text_arab }}
                        </p>
                    @endif

                    <p class="text-sm italic mb-4" style="color: var(--ink-text); opacity: 0.7;">{{ $question->ayat_text_translation }}</p>

                    <p class="font-medium mb-3" style="color: var(--ink-deep);">{{ $question->question_text }}</p>

                    <div class="space-y-1.5">
                        @foreach ($question->options as $option)
                            <div class="flex items-center gap-2 text-sm rounded-md px-3 py-2"
                                 style="{{ $option->is_correct ? 'background-color: var(--parchment); color: var(--ink-deep); font-weight: 600;' : 'color: var(--ink-text); opacity: 0.65;' }}">
                                <span class="w-5">{{ $option->option_label }}.</span>
                                <span>{{ $option->option_text }}</span>
                                @if ($option->is_correct)
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" style="color: var(--gold-leaf);">
                                        <path class="check-draw" d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-quranquiz>
