<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'QuranQuiz' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/css/quranquiz-theme.css', 'resources/js/app.js'])
</head>
<body class="font-body antialiased" style="background-color: var(--parchment); color: var(--ink-text);">

    <div class="min-h-screen flex flex-col">

        <!-- ============ NAVBAR ============ -->
        <header style="background-color: var(--ink-deep); border-bottom: 3px solid var(--gold-leaf);">
            <nav class="max-w-5xl mx-auto px-4 sm:px-6">
                <div class="flex justify-between items-center h-16">

                    <!-- Brand -->
                    <a href="{{ route('quiz.index') }}" class="flex items-center gap-2.5">
                        <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                            <path d="M14 2 L19 9 L14 14 L9 9 Z" fill="#C9A24B"/>
                            <path d="M14 14 L19 19 L14 26 L9 19 Z" fill="#C9A24B" opacity="0.6"/>
                            <circle cx="14" cy="14" r="2.5" fill="#F7F3E8"/>
                        </svg>
                        <span class="font-display text-xl tracking-wide" style="color: var(--parchment);">
                            QuranQuiz
                        </span>
                    </a>

                    <!-- Menu utama (desktop) -->
                    <div class="hidden sm:flex items-center gap-1">
                        @php
                            $navItems = [
                                ['route' => 'quiz.index', 'pattern' => 'quiz.index', 'label' => 'Kuis'],
                                ['route' => 'leaderboard.global', 'pattern' => 'leaderboard.global', 'label' => 'Peringkat'],
                                ['route' => 'admin.generate-questions', 'pattern' => 'admin.generate-questions*', 'label' => 'Generate Soal'],
                            ];
                        @endphp
                        @foreach ($navItems as $item)
                            @if (\Illuminate\Support\Facades\Route::has($item['route']))
                                <a href="{{ route($item['route']) }}"
                                   class="px-4 py-2 text-sm font-medium rounded-md transition-colors duration-150 {{ request()->routeIs($item['pattern']) ? '' : 'hover:bg-white/5' }}"
                                   style="color: {{ request()->routeIs($item['pattern']) ? '#0B3D2E' : '#F7F3E8' }}; {{ request()->routeIs($item['pattern']) ? 'background-color: var(--gold-leaf);' : '' }}">
                                    {{ $item['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>

                    <!-- User menu -->
                    <div class="hidden sm:flex items-center gap-3">
                        @auth
                            <span class="text-sm" style="color: var(--gold-leaf-light);">{{ auth()->user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-sm px-3 py-1.5 rounded-md border transition-colors duration-150 hover:bg-white/5" style="color: var(--parchment); border-color: var(--gold-leaf);">
                                    Keluar
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium px-4 py-1.5 rounded-md" style="background-color: var(--gold-leaf); color: var(--ink-deep);">
                                Masuk
                            </a>
                        @endauth
                    </div>

                    <!-- Mobile toggle -->
                    <button x-data x-on:click="$refs.mobileMenu.classList.toggle('hidden')" class="sm:hidden p-2" style="color: var(--parchment);">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

                <!-- Mobile menu -->
                <div x-ref="mobileMenu" class="hidden sm:hidden pb-4 space-y-1">
                    @foreach ($navItems as $item)
                        @if (\Illuminate\Support\Facades\Route::has($item['route']))
                            <a href="{{ route($item['route']) }}"
                               class="block px-4 py-2 text-sm rounded-md"
                               style="color: {{ request()->routeIs($item['pattern']) ? '#0B3D2E' : '#F7F3E8' }}; {{ request()->routeIs($item['pattern']) ? 'background-color: var(--gold-leaf);' : '' }}">
                                {{ $item['label'] }}
                            </a>
                        @endif
                    @endforeach
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="px-4 pt-2">
                            @csrf
                            <button type="submit" class="text-sm" style="color: var(--gold-leaf-light);">Keluar ({{ auth()->user()->name }})</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="block px-4 py-2 text-sm" style="color: var(--gold-leaf);">Masuk</a>
                    @endauth
                </div>
            </nav>
        </header>

        <!-- ============ KONTEN UTAMA (di tengah) ============ -->
        <main class="flex-1 bg-parchment-texture">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10 md:py-14">
                {{ $slot }}
            </div>
        </main>

        <!-- ============ FOOTER ============ -->
        <footer class="py-6 text-center text-xs" style="background-color: var(--ink-deep); color: var(--gold-leaf-light);">
            QuranQuiz — Tebak Ayat &amp; Tafsir
        </footer>
    </div>
</body>
</html>
