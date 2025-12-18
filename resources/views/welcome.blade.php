<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>EDU TM</title>
    <link rel="icon" href="logo/favicon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: 
                linear-gradient(180deg, rgba(0,0,0,0.45) 0%, rgba(0,0,0,0.25) 40%, rgba(0,0,0,0.15) 100%),
                url('{{ asset('img/gedungB.jpg') }}') center/cover no-repeat fixed;
            color: #111827;
        }

        .fade-in {
            opacity: 0;
            animation: fadeIn 1s ease-out forwards;
        }

        @keyframes fadeIn {
            0% { 
                opacity: 0; 
                transform: translateY(20px); 
            }
            100% { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        .letter {
            animation: fadeIn 1.2s ease-out forwards;
        }
        .letter:nth-child(1) { animation-delay: 0.1s; }
        .letter:nth-child(2) { animation-delay: 0.2s; }
        .letter:nth-child(3) { animation-delay: 0.3s; }
        .letter:nth-child(4) { animation-delay: 0.4s; }
        .letter:nth-child(5) { animation-delay: 0.5s; }

        .author-name {
            opacity: 0;
            animation: fadeIn 1s ease-out forwards;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .author-name:hover {
            transform: translateY(-4px);
            letter-spacing: 0.02em;
        }
    </style>
</head>

<body class="text-gray-900">

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 px-8 py-5 bg-transparent border-transparent">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <img src="{{ asset('logo/EduTMLogo.png') }}" alt="EDU TM" class="h-8 sm:h-9 w-auto" />
            </div>
            
            <nav class="flex items-center gap-3">
                @auth
                    @php
                        $dashboardUrl = route('dashboard');
                        $u = auth()->user();
                        $bySpatie = method_exists($u, 'hasRole');
                        $roleVal = ($u->role instanceof \BackedEnum) ? $u->role->value : ($u->role ?? null);
                        if (($bySpatie && $u->hasRole('master_admin')) || $roleVal === 'master_admin') {
                            $dashboardUrl = route('master.dashboard');
                        } elseif (($bySpatie && $u->hasRole('admin')) || $roleVal === 'admin') {
                            $dashboardUrl = route('admin.dashboard');
                        } elseif (($bySpatie && $u->hasRole('guru')) || $roleVal === 'guru') {
                            $dashboardUrl = route('guru.dashboard');
                        }
                    @endphp
                    {{-- Navigation untuk user yang sudah login --}}
                    <a href="{{ $dashboardUrl }}" class="px-6 py-2 rounded-full text-sm font-medium bg-gray-900 text-white hover:bg-gray-800 transition">
                        Dashboard
                    </a>
                @else
                    {{-- Navigation untuk user yang belum login --}}
                    <a href="{{ url('/login') }}" class="px-6 py-2 rounded-full text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-500 shadow-lg shadow-indigo-500/30 transition">
                        Masuk
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex flex-col items-center justify-center min-h-screen px-8 pt-24">
        <div class="max-w-4xl w-full mx-auto text-center fade-in" style="animation-delay: 0.3s;">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white mb-5 tracking-tight drop-shadow-md">
                Selamat Datang di EDU TM
            </h1>
            <p class="text-white/90 text-base sm:text-lg leading-relaxed max-w-2xl mx-auto drop-shadow">
                Platform manajemen akademik modern untuk sekolah. Kelola siswa, nilai, dan sertifikat dengan cepat dan mudah.
            </p>
            <p class="text-white/90 text-base sm:text-lg leading-relaxed max-w-2xl mx-auto drop-shadow">
                Silakan login untuk mulai mengelola data siswa, penilaian, dan aktivitas pembelajaran.
            </p>

            @guest
                <div class="mt-8 flex items-center justify-center">
                    <a href="{{ url('/login') }}" class="px-7 py-3 rounded-full text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-500 shadow-lg shadow-indigo-500/30 transition">
                        Masuk ke Aplikasi
                    </a>
                </div>
            @endguest
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-4 bottom-0 left-0 right-0 py-4 text-center bg-transparent">
        <p class="text-sm text-white/80">2025 EDU TM — All rights reserved.</p>
    </footer>

</body>
</html>