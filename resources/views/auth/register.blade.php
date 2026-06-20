<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar — QuranQuiz</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/quranquiz-theme.css', 'resources/css/quranquiz-animations.css', 'resources/js/app.js'])
    <style>
        @keyframes ambientDrift {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-8px, 8px); }
        }
        @media (prefers-reduced-motion: no-preference) {
            #bg-motif { animation: ambientDrift 18s ease-in-out infinite; }
        }
    </style>
</head>
<body class="font-body antialiased min-h-screen flex items-center justify-center px-4 py-10" style="background-color: var(--ink-deep);">

    <!-- Motif geometris dekoratif latar belakang, drift halus -->
    <svg id="bg-motif" class="fixed top-0 left-0 w-full h-full pointer-events-none" style="opacity: 0.04;" preserveAspectRatio="none">
        <pattern id="star-pattern" width="80" height="80" patternUnits="userSpaceOnUse">
            <path d="M40 5 L55 25 L40 45 L25 25 Z M40 35 L55 55 L40 75 L25 55 Z" fill="none" stroke="#C9A24B" stroke-width="1"/>
        </pattern>
        <rect width="100%" height="100%" fill="url(#star-pattern)"/>
    </svg>

    <div class="relative w-full max-w-md anim-fade-up">

        <!-- Brand di atas kartu -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full mb-3" style="background-color: var(--gold-leaf);">
                <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                    <path d="M14 2 L19 9 L14 14 L9 9 Z" fill="#0B3D2E"/>
                    <path d="M14 14 L19 19 L14 26 L9 19 Z" fill="#0B3D2E" opacity="0.6"/>
                    <circle cx="14" cy="14" r="2.5" fill="#F7F3E8"/>
                </svg>
            </div>
            <h1 class="font-display text-3xl" style="color: var(--parchment);">QuranQuiz</h1>
            <p class="text-sm mt-1" style="color: var(--gold-leaf-light);">Tebak Ayat &amp; Tafsir</p>
        </div>

        <!-- Kartu register dengan bingkai manuskrip -->
        <div class="manuscript-frame rounded-sm p-8 sm:p-10" style="background-color: var(--parchment);">

            <h2 class="font-display text-xl mb-6 text-center" style="color: var(--ink-deep);">Buat Akun Baru</h2>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium mb-1.5" style="color: var(--ink-text);">Nama</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                           class="w-full rounded-md px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                           style="border: 1px solid var(--parchment-border); background-color: white; --tw-ring-color: var(--gold-leaf); transition: border-color 0.2s ease;">
                    @error('name')
                        <p class="mt-1.5 text-xs" style="color: var(--maroon);">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium mb-1.5" style="color: var(--ink-text);">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                           class="w-full rounded-md px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                           style="border: 1px solid var(--parchment-border); background-color: white; --tw-ring-color: var(--gold-leaf); transition: border-color 0.2s ease;">
                    @error('email')
                        <p class="mt-1.5 text-xs" style="color: var(--maroon);">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium mb-1.5" style="color: var(--ink-text);">Kata Sandi</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="w-full rounded-md px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                           style="border: 1px solid var(--parchment-border); background-color: white; --tw-ring-color: var(--gold-leaf); transition: border-color 0.2s ease;">
                    @error('password')
                        <p class="mt-1.5 text-xs" style="color: var(--maroon);">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium mb-1.5" style="color: var(--ink-text);">Konfirmasi Kata Sandi</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full rounded-md px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                           style="border: 1px solid var(--parchment-border); background-color: white; --tw-ring-color: var(--gold-leaf); transition: border-color 0.2s ease;">
                    @error('password_confirmation')
                        <p class="mt-1.5 text-xs" style="color: var(--maroon);">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="btn-animated w-full rounded-md py-3 text-sm font-semibold"
                        style="background-color: var(--ink-deep); color: var(--parchment);">
                    Daftar
                </button>
            </form>

            <p class="text-center text-sm mt-6" style="color: var(--ink-text);">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-semibold hover:underline" style="color: var(--maroon);">Masuk di sini</a>
            </p>
        </div>
    </div>
</body>
</html>
