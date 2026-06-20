<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-emerald-900 leading-tight">Hasil Kuis</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if (session('error'))
                <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-rose-700 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-amber-50 p-8 text-center mb-8">
                <p class="text-sm uppercase tracking-widest text-amber-600 font-semibold mb-2">{{ $attempt->quiz->title }}</p>
                <p class="text-5xl font-bold text-emerald-900 mb-1">{{ $attempt->score }}</p>
                <p class="text-sm text-emerald-700/80 mb-6">Total Poin</p>

                <div class="flex justify-center gap-8 text-sm">
                    <div>
                        <p class="font-bold text-emerald-700 text-lg">{{ $attempt->correct_count }}</p>
                        <p class="text-emerald-700/70">Benar</p>
                    </div>
                    <div>
                        <p class="font-bold text-rose-600 text-lg">{{ $attempt->wrong_count }}</p>
                        <p class="text-rose-600/70">Salah</p>
                    </div>
                    <div>
                        <p class="font-bold text-amber-700 text-lg capitalize">{{ $attempt->status }}</p>
                        <p class="text-amber-700/70">Status</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 mb-8">
                <a href="{{ route('quiz.leaderboard', $attempt->quiz_id) }}"
                   class="flex-1 text-center rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 transition-colors">
                    Lihat Peringkat
                </a>
                <a href="{{ route('quiz.index') }}"
                   class="flex-1 text-center rounded-lg border border-emerald-200 px-4 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-50 transition-colors">
                    Kuis Lainnya
                </a>
            </div>

            <h3 class="font-semibold text-emerald-950 mb-4">Rincian Jawaban</h3>
            <div class="space-y-3">
                @foreach ($attempt->answers as $answer)
                    <div class="rounded-lg border {{ $answer->is_correct ? 'border-emerald-200 bg-emerald-50/50' : 'border-rose-200 bg-rose-50/50' }} px-4 py-3">
                        <div class="flex justify-between items-start gap-3">
                            <p class="text-sm text-emerald-950 flex-1">{{ $answer->question->question_text }}</p>
                            <span class="text-xs font-bold whitespace-nowrap {{ $answer->is_correct ? 'text-emerald-700' : 'text-rose-600' }}">
                                {{ $answer->is_correct ? '+' . $answer->points_earned : '0' }} pts
                            </span>
                        </div>
                        <p class="text-xs text-emerald-700/70 mt-1">
                            Jawabanmu: {{ $answer->option?->option_text ?? '(tidak menjawab)' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
