<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-emerald-900 leading-tight">
            Preview Soal — {{ $quiz->title }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <a href="{{ route('admin.generate-questions') }}" class="inline-block mb-6 text-sm font-semibold text-emerald-700 hover:text-emerald-900">
                ← Kembali ke Generate Soal
            </a>

            @if ($quiz->questions->isEmpty())
                <div class="rounded-xl border border-dashed border-emerald-200 bg-emerald-50/50 p-10 text-center">
                    <p class="text-emerald-700">Belum ada soal pada quiz ini.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($quiz->questions as $index => $question)
                        <div class="rounded-xl border border-emerald-100 bg-white shadow-sm p-5">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-amber-600 uppercase tracking-wide">
                                    Soal {{ $index + 1 }} &middot; {{ $question->question_type }}
                                </span>
                                <span class="text-xs text-emerald-700/70">
                                    QS. {{ $question->surah_name }} : {{ $question->ayat_number }}
                                </span>
                            </div>

                            @if ($question->ayat_text_arab)
                                <p dir="rtl" class="text-lg text-right text-emerald-950 mb-2 font-arabic">
                                    {{ $question->ayat_text_arab }}
                                </p>
                            @endif

                            <p class="text-sm italic text-emerald-700/80 mb-3">{{ $question->ayat_text_translation }}</p>

                            <p class="font-medium text-emerald-950 mb-3">{{ $question->question_text }}</p>

                            <div class="space-y-1.5">
                                @foreach ($question->options as $option)
                                    <div class="flex items-center gap-2 text-sm rounded-lg px-3 py-2 {{ $option->is_correct ? 'bg-emerald-50 text-emerald-800 font-semibold' : 'text-emerald-900/70' }}">
                                        <span class="w-5">{{ $option->option_label }}.</span>
                                        <span>{{ $option->option_text }}</span>
                                        @if ($option->is_correct)
                                            <span class="text-xs text-emerald-600">✓ benar</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
