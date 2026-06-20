<x-app-quranquiz>

    <div class="text-center mb-10 anim-fade-up">
        <p class="text-xs uppercase tracking-[0.2em] font-semibold mb-3" style="color: var(--maroon);">Integrasi API EQuran.id</p>
        <h1 class="font-display text-3xl" style="color: var(--ink-deep);">Generate Soal Kuis</h1>
    </div>

    @if (session('success'))
        <div class="mb-6 text-sm rounded-md px-4 py-3 anim-fade-up" style="background-color: #E8F0E9; color: var(--ink-deep); border: 1px solid var(--ink-deep);">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 text-sm rounded-md px-4 py-3 anim-fade-up" style="background-color: #F5E6E6; color: var(--maroon); border: 1px solid var(--maroon);">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 text-sm rounded-md px-4 py-3 anim-fade-up" style="background-color: #F5E6E6; color: var(--maroon); border: 1px solid var(--maroon);">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Buat Quiz Baru -->
    <div class="card-lift rounded-sm p-6 mb-6 anim-fade-up" style="background-color: white; border: 1px solid var(--parchment-border); border-top: 3px solid var(--gold-leaf); animation-delay: 0.04s;">
        <h3 class="font-display text-lg mb-1" style="color: var(--ink-deep);">1. Buat Quiz Baru</h3>
        <p class="text-sm mb-4" style="color: var(--ink-text); opacity: 0.6;">Opsional — lewati kalau quiz sudah ada.</p>

        <form method="POST" action="{{ route('admin.generate-questions.store-quiz') }}" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <input type="text" name="title" placeholder="Judul quiz, contoh: QuranQuiz - Surah Pendek"
                   class="flex-1 rounded-md px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                   style="border: 1px solid var(--parchment-border); --tw-ring-color: var(--gold-leaf); transition: border-color 0.2s ease;" required>
            <input type="number" name="seconds_per_question" value="30" min="5" max="300"
                   class="w-full sm:w-32 rounded-md px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                   style="border: 1px solid var(--parchment-border); --tw-ring-color: var(--gold-leaf); transition: border-color 0.2s ease;"
                   title="Detik per soal" required>
            <button type="submit"
                    class="btn-animated rounded-md px-5 py-2.5 text-sm font-semibold whitespace-nowrap"
                    style="background-color: var(--ink-deep); color: var(--parchment);">
                + Buat Quiz
            </button>
        </form>
    </div>

    <!-- Generate Soal -->
    <div class="card-lift rounded-sm p-6 mb-6 anim-fade-up" style="background-color: white; border: 1px solid var(--parchment-border); border-top: 3px solid var(--gold-leaf); animation-delay: 0.08s;">
        <h3 class="font-display text-lg mb-1" style="color: var(--ink-deep);">2. Generate Soal dari API EQuran.id</h3>
        <p class="text-sm mb-4" style="color: var(--ink-text); opacity: 0.6;">
            Setiap soal menarik 1 ayat acak — tebak surah, tebak lanjutan ayat, atau tebak nomor ayat.
        </p>

        @if ($quizzes->isEmpty())
            <p class="text-sm rounded-md px-4 py-3" style="background-color: var(--parchment); color: var(--maroon);">
                Belum ada quiz. Buat quiz baru dulu di langkah 1.
            </p>
        @else
            <form method="POST" action="{{ route('admin.generate-questions.generate') }}" id="generate-form">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color: var(--ink-text);">Pilih Quiz</label>
                        <select name="quiz_id" class="w-full rounded-md px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                                style="border: 1px solid var(--parchment-border); --tw-ring-color: var(--gold-leaf);" required>
                            @foreach ($quizzes as $quiz)
                                <option value="{{ $quiz->id }}">{{ $quiz->title }} ({{ $quiz->questions_count }} soal saat ini)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color: var(--ink-text);">Jumlah Soal Baru</label>
                        <input type="number" name="count" value="5" min="1" max="50"
                               class="w-full rounded-md px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                               style="border: 1px solid var(--parchment-border); --tw-ring-color: var(--gold-leaf);" required>
                    </div>
                </div>

                <label class="flex items-center gap-2 mb-5 text-sm" style="color: var(--ink-text);">
                    <input type="checkbox" name="fresh" value="1" class="rounded" style="accent-color: var(--ink-deep);">
                    Hapus semua soal lama pada quiz ini sebelum generate (reset)
                </label>

                <button type="submit" id="generate-btn"
                        class="btn-animated w-full rounded-md px-5 py-3 text-sm font-semibold"
                        style="background-color: var(--gold-leaf); color: var(--ink-deep);">
                    Generate Soal Sekarang
                </button>
                <p class="text-xs mt-2 text-center" style="color: var(--ink-text); opacity: 0.55;">
                    Proses memanggil API satu per satu — untuk jumlah besar (&gt;20), mohon tunggu, jangan tutup halaman.
                </p>
            </form>
        @endif
    </div>

    <!-- Daftar Quiz -->
    <div class="card-lift rounded-sm overflow-hidden anim-fade-up" style="background-color: white; border: 1px solid var(--parchment-border); border-top: 3px solid var(--gold-leaf); animation-delay: 0.12s;">
        <h3 class="font-display text-lg p-6 pb-0" style="color: var(--ink-deep);">Daftar Quiz</h3>
        @if ($quizzes->isEmpty())
            <p class="p-6 text-sm" style="color: var(--ink-text); opacity: 0.6;">Belum ada quiz.</p>
        @else
            <div class="anim-stagger">
                @foreach ($quizzes as $quiz)
                    <div class="flex items-center justify-between px-6 py-4" style="border-top: 1px solid var(--parchment-border);">
                        <div>
                            <p class="font-medium" style="color: var(--ink-text);">{{ $quiz->title }}</p>
                            <p class="text-xs mt-0.5" style="color: var(--ink-text); opacity: 0.55;">{{ $quiz->questions_count }} soal &middot; {{ $quiz->seconds_per_question }}s/soal</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.generate-questions.preview', $quiz) }}"
                               class="btn-animated rounded-md px-3 py-1.5 text-xs font-semibold transition-colors hover:bg-amber-50"
                               style="border: 1px solid var(--gold-leaf); color: var(--ink-deep);">
                                Lihat Soal
                            </a>
                            <form method="POST" action="{{ route('admin.generate-questions.destroy-quiz', $quiz) }}"
                                  onsubmit="return confirm('Hapus quiz \'{{ $quiz->title }}\' beserta semua soalnya?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn-animated rounded-md px-3 py-1.5 text-xs font-semibold transition-colors hover:bg-red-50"
                                        style="border: 1px solid var(--maroon); color: var(--maroon);">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        document.getElementById('generate-form')?.addEventListener('submit', function () {
            const btn = document.getElementById('generate-btn');
            btn.disabled = true;
            btn.textContent = 'Sedang mengambil ayat dari API, mohon tunggu...';
            btn.classList.add('btn-loading');
            btn.style.cursor = 'not-allowed';
        });
    </script>
</x-app-quranquiz>
