<x-app-quranquiz>

    <!-- Progress & Timer -->
    <div class="flex items-center justify-between mb-3 anim-fade-up">
        <span class="text-sm font-semibold" style="color: var(--ink-deep);">
            Soal {{ $questionNumber }} dari {{ $quiz->questions->count() }}
        </span>
        <div id="timer-badge" class="flex items-center gap-2 px-3 py-1 rounded-full" style="background-color: white; border: 1px solid var(--gold-leaf);">
            <svg class="w-4 h-4" style="color: var(--maroon);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span id="timer" class="text-sm font-bold tabular-nums" style="color: var(--maroon);">--:--</span>
        </div>
    </div>

    <!-- Progress bar -->
    <div class="w-full h-1.5 rounded-full mb-8 overflow-hidden anim-fade-up" style="background-color: var(--parchment-border);">
        <div class="h-full rounded-full progress-fill" style="background-color: var(--gold-leaf); width: {{ ($questionNumber - 1) / $quiz->questions->count() * 100 }}%"></div>
    </div>

    <!-- Soal Card dengan bingkai manuskrip -->
    <div class="manuscript-frame rounded-sm p-8 anim-fade-up" style="background-color: white; animation-delay: 0.08s;">

        @if ($question->surah_name)
            <p class="text-xs uppercase tracking-[0.15em] font-semibold mb-4" style="color: var(--maroon);">
                QS. {{ $question->surah_name }} : {{ $question->ayat_number }}
            </p>
        @endif

        @if ($question->ayat_text_arab)
            <p dir="rtl" class="font-display text-2xl sm:text-3xl text-right leading-relaxed mb-4" style="color: var(--ink-deep);">
                {{ $question->ayat_text_arab }}
            </p>
        @endif

        @if ($question->ayat_text_translation)
            <p class="text-sm italic mb-6 pb-6" style="color: var(--ink-text); opacity: 0.75; border-bottom: 1px dashed var(--parchment-border);">
                &ldquo;{{ $question->ayat_text_translation }}&rdquo;
            </p>
        @endif

        <p class="font-display text-lg mb-5" style="color: var(--ink-deep);">{{ $question->question_text }}</p>

        <form id="answer-form" method="POST" action="{{ route('quiz.answer', $attempt->id) }}">
            @csrf
            <input type="hidden" name="question_id" value="{{ $question->id }}">
            <input type="hidden" name="answer_time_seconds" id="answer_time_seconds" value="0">

            <div class="space-y-2.5">
                @foreach ($question->options as $option)
                    <label class="option-row flex items-center gap-3 rounded-md px-4 py-3 cursor-pointer"
                           style="border: 1px solid var(--parchment-border);"
                           onmouseover="if(!this.querySelector('input').checked) this.style.borderColor='var(--gold-leaf)'"
                           onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='var(--parchment-border)'">
                        <input type="radio" name="option_id" value="{{ $option->id }}" required
                               onclick="document.querySelectorAll('#answer-form label').forEach(l => { l.style.borderColor='var(--parchment-border)'; l.style.backgroundColor=''; }); this.closest('label').style.borderColor='var(--ink-deep)'; this.closest('label').style.backgroundColor='var(--parchment)';"
                               class="w-4 h-4" style="accent-color: var(--ink-deep);">
                        <span class="font-semibold w-5" style="color: var(--maroon);">{{ $option->option_label }}.</span>
                        <span style="color: var(--ink-text);">{{ $option->option_text }}</span>
                    </label>
                @endforeach
            </div>

            <button type="submit" id="submit-btn"
                    class="btn-animated mt-7 w-full rounded-md py-3 text-sm font-semibold"
                    style="background-color: var(--ink-deep); color: var(--parchment);">
                Jawab
            </button>
        </form>
    </div>

    <script>
        (function () {
            const totalRemaining = {{ $remainingSeconds }};
            const secondsPerQuestion = {{ $quiz->seconds_per_question }};
            let questionSeconds = Math.min(totalRemaining, secondsPerQuestion);
            const startedQuestionAt = Date.now();

            const timerEl = document.getElementById('timer');
            const timerBadge = document.getElementById('timer-badge');
            const answerTimeInput = document.getElementById('answer_time_seconds');
            const form = document.getElementById('answer-form');
            const submitBtn = document.getElementById('submit-btn');

            function render(seconds) {
                const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                const s = (seconds % 60).toString().padStart(2, '0');
                timerEl.textContent = `${m}:${s}`;

                // Rasa tegang: pulse + warna lebih pekat saat waktu hampir habis
                if (seconds <= 5) {
                    timerBadge.classList.add('is-urgent');
                    timerBadge.style.borderColor = 'var(--maroon)';
                    timerBadge.style.backgroundColor = '#FBEFEF';
                } else {
                    timerBadge.classList.remove('is-urgent');
                    timerBadge.style.borderColor = 'var(--gold-leaf)';
                    timerBadge.style.backgroundColor = 'white';
                }
            }

            render(questionSeconds);

            const interval = setInterval(() => {
                questionSeconds -= 1;
                render(Math.max(0, questionSeconds));

                if (questionSeconds <= 0) {
                    clearInterval(interval);
                    answerTimeInput.value = secondsPerQuestion;
                    const optionChecked = form.querySelector('input[name="option_id"]:checked');
                    if (!optionChecked) {
                        form.querySelectorAll('input[name="option_id"]').forEach(el => el.required = false);
                    }
                    form.submit();
                }
            }, 1000);

            form.addEventListener('submit', () => {
                clearInterval(interval);
                const elapsed = Math.round((Date.now() - startedQuestionAt) / 1000);
                answerTimeInput.value = Math.min(elapsed, secondsPerQuestion);

                // Feedback halus: tombol menunjukkan sedang memproses
                submitBtn.textContent = 'Memeriksa jawaban...';
                submitBtn.style.opacity = '0.75';
                submitBtn.disabled = true;
            });
        })();
    </script>
</x-app-quranquiz>
