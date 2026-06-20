<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-emerald-900 leading-tight">
            {{ $quiz->title }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            <!-- Progress & Timer -->
            <div class="flex items-center justify-between mb-6">
                <span class="text-sm font-semibold text-emerald-700">
                    Soal {{ $questionNumber }} / {{ $quiz->questions->count() }}
                </span>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span id="timer" class="text-sm font-bold text-amber-700 tabular-nums">--:--</span>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="w-full h-1.5 bg-emerald-100 rounded-full mb-8 overflow-hidden">
                <div class="h-full bg-emerald-600 rounded-full transition-all"
                     style="width: {{ ($questionNumber - 1) / $quiz->questions->count() * 100 }}%"></div>
            </div>

            <!-- Soal Card -->
            <div class="rounded-2xl border border-emerald-100 bg-white shadow-sm p-8">
                @if ($question->surah_name)
                    <p class="text-xs uppercase tracking-widest text-amber-600 font-semibold mb-3">
                        QS. {{ $question->surah_name }} : {{ $question->ayat_number }}
                    </p>
                @endif

                @if ($question->ayat_text_arab)
                    <p dir="rtl" class="text-2xl text-right leading-relaxed text-emerald-950 font-arabic mb-4">
                        {{ $question->ayat_text_arab }}
                    </p>
                @endif

                @if ($question->ayat_text_translation)
                    <p class="text-sm text-emerald-700/80 italic mb-6">
                        "{{ $question->ayat_text_translation }}"
                    </p>
                @endif

                <p class="font-semibold text-emerald-950 mb-5">{{ $question->question_text }}</p>

                <form id="answer-form" method="POST" action="{{ route('quiz.answer', $attempt->id) }}">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $question->id }}">
                    <input type="hidden" name="answer_time_seconds" id="answer_time_seconds" value="0">

                    <div class="space-y-3">
                        @foreach ($question->options as $option)
                            <label class="flex items-center gap-3 rounded-lg border border-emerald-100 px-4 py-3 cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50 transition-colors [&:has(input:checked)]:border-emerald-600 [&:has(input:checked)]:bg-emerald-50">
                                <input type="radio" name="option_id" value="{{ $option->id }}" required
                                       class="w-4 h-4 text-emerald-700 focus:ring-emerald-500">
                                <span class="font-semibold text-emerald-800 w-5">{{ $option->option_label }}.</span>
                                <span class="text-emerald-950">{{ $option->option_text }}</span>
                            </label>
                        @endforeach
                    </div>

                    <button type="submit"
                            class="mt-6 w-full rounded-lg bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-800 transition-colors">
                        Jawab
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const totalRemaining = {{ $remainingSeconds }};
            const secondsPerQuestion = {{ $quiz->seconds_per_question }};
            let questionSeconds = Math.min(totalRemaining, secondsPerQuestion);
            const startedQuestionAt = Date.now();

            const timerEl = document.getElementById('timer');
            const answerTimeInput = document.getElementById('answer_time_seconds');
            const form = document.getElementById('answer-form');

            function render(seconds) {
                const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                const s = (seconds % 60).toString().padStart(2, '0');
                timerEl.textContent = `${m}:${s}`;
                timerEl.classList.toggle('text-rose-600', seconds <= 5);
            }

            render(questionSeconds);

            const interval = setInterval(() => {
                questionSeconds -= 1;
                render(Math.max(0, questionSeconds));

                if (questionSeconds <= 0) {
                    clearInterval(interval);
                    // Waktu habis -> auto-submit tanpa jawaban (option_id kosong)
                    answerTimeInput.value = secondsPerQuestion;
                    const optionChecked = form.querySelector('input[name="option_id"]:checked');
                    if (!optionChecked) {
                        // izinkan submit tanpa pilihan saat waktu habis
                        form.querySelectorAll('input[name="option_id"]').forEach(el => el.required = false);
                    }
                    form.submit();
                }
            }, 1000);

            form.addEventListener('submit', () => {
                clearInterval(interval);
                const elapsed = Math.round((Date.now() - startedQuestionAt) / 1000);
                answerTimeInput.value = Math.min(elapsed, secondsPerQuestion);
            });
        })();
    </script>
</x-app-layout>
