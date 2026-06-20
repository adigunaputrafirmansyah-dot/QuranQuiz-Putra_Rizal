<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-emerald-900 leading-tight">
            Papan Peringkat Global
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            <p class="text-sm text-emerald-700/70 mb-6">
                Total skor terkumpul dari seluruh kuis yang sudah diselesaikan.
            </p>

            <div class="rounded-2xl border border-emerald-100 bg-white shadow-sm overflow-hidden">
                @if ($top->isEmpty())
                    <div class="p-10 text-center text-emerald-700/70">
                        Belum ada yang menyelesaikan kuis apa pun.
                    </div>
                @else
                    @foreach ($top as $index => $row)
                        <div class="flex items-center gap-4 px-6 py-4 {{ $index < $top->count() - 1 ? 'border-b border-emerald-50' : '' }} {{ $index === 0 ? 'bg-gradient-to-r from-amber-50 to-transparent' : '' }}">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                                {{ $index === 0 ? 'bg-amber-400 text-white' : ($index === 1 ? 'bg-emerald-200 text-emerald-800' : ($index === 2 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-50 text-emerald-600')) }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-emerald-950">{{ $row->user->name ?? 'Pengguna' }}</p>
                                <p class="text-xs text-emerald-700/70">{{ $row->attempts_count }} kuis diselesaikan</p>
                            </div>
                            <p class="font-bold text-emerald-800">{{ $row->total_score }} pts</p>
                        </div>
                    @endforeach
                @endif
            </div>

            <a href="{{ route('quiz.index') }}" class="inline-block mt-6 text-sm font-semibold text-emerald-700 hover:text-emerald-900">
                ← Kembali ke daftar kuis
            </a>
        </div>
    </div>
</x-app-layout>
