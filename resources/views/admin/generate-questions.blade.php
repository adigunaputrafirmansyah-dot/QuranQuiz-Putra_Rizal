<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-emerald-900 leading-tight">
            Generate Soal Kuis (Integrasi API EQuran.id)
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('success'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-emerald-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-rose-700 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-rose-700 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Buat Quiz Baru -->
            <div class="rounded-2xl border border-emerald-100 bg-white shadow-sm p-6">
                <h3 class="font-semibold text-emerald-950 mb-1">1. Buat Quiz Baru (opsional)</h3>
                <p class="text-sm text-emerald-700/70 mb-4">Lewati langkah ini kalau quiz sudah ada dan tinggal diisi soal.</p>

                <form method="POST" action="{{ route('admin.generate-questions.store-quiz') }}" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <input type="text" name="title" placeholder="Judul quiz, contoh: QuranQuiz - Surah Pendek"
                           class="flex-1 rounded-lg border border-emerald-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                           required>
                    <input type="number" name="seconds_per_question" value="30" min="5" max="300"
                           class="w-full sm:w-32 rounded-lg border border-emerald-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                           title="Detik per soal" required>
                    <button type="submit"
                            class="rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 transition-colors whitespace-nowrap">
                        + Buat Quiz
                    </button>
                </form>
            </div>

            <!-- Generate Soal -->
            <div class="rounded-2xl border border-emerald-100 bg-white shadow-sm p-6">
                <h3 class="font-semibold text-emerald-950 mb-1">2. Generate Soal dari API EQuran.id</h3>
                <p class="text-sm text-emerald-700/70 mb-4">
                    Setiap soal menarik 1 ayat acak dan membuat 3 variasi tipe soal (tebak surah, tebak lanjutan ayat, tebak nomor ayat).
                </p>

                @if ($quizzes->isEmpty())
                    <p class="text-sm text-amber-700 bg-amber-50 rounded-lg px-4 py-3">
                        Belum ada quiz. Buat quiz baru dulu di langkah 1 di atas.
                    </p>
                @else
                    <form method="POST" action="{{ route('admin.generate-questions.generate') }}" id="generate-form">
                        @csrf
                        <div class="grid sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-emerald-800 mb-1">Pilih Quiz</label>
                                <select name="quiz_id" class="w-full rounded-lg border border-emerald-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                                    @foreach ($quizzes as $quiz)
                                        <option value="{{ $quiz->id }}">
                                            {{ $quiz->title }} ({{ $quiz->questions_count }} soal saat ini)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-emerald-800 mb-1">Jumlah Soal Baru</label>
                                <input type="number" name="count" value="5" min="1" max="50"
                                       class="w-full rounded-lg border border-emerald-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                            </div>
                        </div>

                        <label class="flex items-center gap-2 mb-5 text-sm text-emerald-800">
                            <input type="checkbox" name="fresh" value="1" class="rounded text-emerald-700 focus:ring-emerald-500">
                            Hapus semua soal lama pada quiz ini sebelum generate (reset)
                        </label>

                        <button type="submit" id="generate-btn"
                                class="w-full rounded-lg bg-amber-600 px-5 py-3 text-sm font-semibold text-white hover:bg-amber-700 transition-colors">
                            Generate Soal Sekarang
                        </button>
                        <p class="text-xs text-emerald-700/60 mt-2 text-center">
                            Proses memanggil API EQuran.id satu per satu — untuk jumlah besar (&gt;20), proses bisa memakan waktu beberapa detik. Mohon tunggu sampai selesai, jangan tutup halaman.
                        </p>
                    </form>
                @endif
            </div>

            <!-- Daftar Quiz & Aksi -->
            <div class="rounded-2xl border border-emerald-100 bg-white shadow-sm overflow-hidden">
                <h3 class="font-semibold text-emerald-950 p-6 pb-0">Daftar Quiz</h3>
                @if ($quizzes->isEmpty())
                    <p class="p-6 text-sm text-emerald-700/70">Belum ada quiz.</p>
                @else
                    <div class="divide-y divide-emerald-50">
                        @foreach ($quizzes as $quiz)
                            <div class="flex items-center justify-between px-6 py-4">
                                <div>
                                    <p class="font-medium text-emerald-950">{{ $quiz->title }}</p>
                                    <p class="text-xs text-emerald-700/70">{{ $quiz->questions_count }} soal &middot; {{ $quiz->seconds_per_question }}s/soal</p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.generate-questions.preview', $quiz) }}"
                                       class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 transition-colors">
                                        Lihat Soal
                                    </a>
                                    <form method="POST" action="{{ route('admin.generate-questions.destroy-quiz', $quiz) }}"
                                          onsubmit="return confirm('Hapus quiz \'{{ $quiz->title }}\' beserta semua soalnya? Tindakan ini tidak bisa dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.getElementById('generate-form')?.addEventListener('submit', function () {
            const btn = document.getElementById('generate-btn');
            btn.disabled = true;
            btn.textContent = 'Sedang mengambil ayat dari API, mohon tunggu...';
            btn.classList.add('opacity-70', 'cursor-not-allowed');
        });
    </script>
</x-app-layout>
