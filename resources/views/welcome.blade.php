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
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 50%, #f1f3f5 100%);
            min-height: 100vh;
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
    <header class="fixed top-0 left-0 right-0 z-50 px-8 py-5 bg-white/70 backdrop-blur-xl border-b border-gray-200/50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="text-xl font-semibold tracking-tight text-gray-900">EDU TM</div>
            
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
                    <a href="{{ url('/login') }}" class="px-6 py-2 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                        Log in
                    </a>
                    <a href="{{ url('/register') }}" class="px-6 py-2 rounded-full text-sm font-medium bg-gray-900 text-white hover:bg-gray-800 transition">
                        Register
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex flex-col items-center justify-center min-h-screen px-8 pt-24">
        
        <div class="max-w-6xl w-full mx-auto text-center">

            <!-- Logo Letters -->
            <div class="flex justify-center gap-4 lg:gap-8 mb-20">
                <h1 class="letter opacity-0 font-black text-gray-900" 
                    style="font-size: clamp(5rem, 16vw, 12rem); letter-spacing: -0.03em;">
                    E
                </h1>
                <h1 class="letter opacity-0 font-black text-gray-900" 
                    style="font-size: clamp(5rem, 16vw, 12rem); letter-spacing: -0.03em;">
                    D
                </h1>
                <h1 class="letter opacity-0 font-black text-gray-900" 
                    style="font-size: clamp(5rem, 16vw, 12rem); letter-spacing: -0.03em;">
                    U
                </h1>
                <h1 class="letter opacity-0 font-black text-gray-900" 
                    style="font-size: clamp(5rem, 16vw, 12rem); letter-spacing: -0.03em;">
                    T
                </h1>
                <h1 class="letter opacity-0 font-black text-gray-900" 
                    style="font-size: clamp(5rem, 16vw, 12rem); letter-spacing: -0.03em;">
                    M
                </h1>
            </div>

            <!-- Divider Line -->
            <div class="w-24 h-px bg-gray-300 mx-auto mb-16 fade-in" style="animation-delay: 0.7s;"></div>

            <!-- Subtitle -->
            <p class="text-sm uppercase tracking-wider text-gray-500 font-medium mb-12 fade-in" style="animation-delay: 0.9s;">
                Created by
            </p>

            <!-- Main Author -->
            <div class="mb-12">
                <h2 class="author-name text-6xl lg:text-8xl font-light text-gray-800" 
                    style="animation-delay: 1.1s; letter-spacing: 0.05em;">
                    TEGAR
                </h2>
                <p class="text-base lg:text-lg text-gray-500 font-medium mt-3 fade-in" style="animation-delay: 1.3s;">
                    Project Lead
                </p>
            </div>

            <!-- Divider Line -->
            <div class="w-32 h-px bg-gray-300 mx-auto my-16 fade-in" style="animation-delay: 1.5s;"></div>

            <!-- Support Team -->
            <div class="mb-6">
                <p class="text-sm uppercase tracking-wider text-gray-500 font-medium mb-8 fade-in" style="animation-delay: 1.7s;">
                    Support Team
                </p>
                
                <div class="flex flex-col lg:flex-row justify-center items-center gap-8 lg:gap-16">
                    <!-- AISYAH -->
                    <h3 class="author-name text-4xl lg:text-5xl font-light text-gray-700" 
                        style="animation-delay: 1.9s; letter-spacing: 0.05em;">
                        AISYAH
                    </h3>

                    <!-- KAYRA -->
                    <h3 class="author-name text-4xl lg:text-5xl font-light text-gray-700" 
                        style="animation-delay: 2.1s; letter-spacing: 0.05em;">
                        KAYRA
                    </h3>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-4 bottom-0 left-0 right-0 py-4 text-center bg-white/70 backdrop-blur-xl border-t border-gray-200/50">
        <p class="text-sm text-gray-500">© 2025 EDU TM — All rights reserved.</p>
    </footer>

</body>
</html>