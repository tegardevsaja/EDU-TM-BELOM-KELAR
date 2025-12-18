<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen antialiased dark:bg-gradient-to-b dark:from-neutral-950 dark:to-neutral-900">

    <div class="flex min-h-screen w-full justify-between">
        <!-- Bagian Form / Konten Kiri -->
        <div class="w-full lg:w-2/5 flex flex-col justify-between py-8 lg:py-12 px-4">
            <div class="w-full max-w-md mx-auto flex flex-col gap-6 rounded-lg p-6">
                <div class="flex justify-start">
                    <img src="{{ asset('logo/EduTMLogo.png') }}" alt="Logo EduTM" class="w-28 lg:w-32 h-auto">
                </div>
                {{ $slot }}
            </div>
            
            <!-- Copyright Section - Kiri Bawah -->
            <div class="w-full max-w-md mx-auto px-6 mt-8">
                <div class="text-center lg:text-left">
                    <p class="text-xs lg:text-sm text-zinc-500 dark:text-zinc-400">
                        &copy; {{ date('Y') }} EduTM. All rights reserved.
                    </p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">
                        Version 1.0.0
                    </p>
                </div>
            </div>
        </div>

        <!-- Bagian Foto / Kanan -->
        <div class="hidden lg:flex lg:w-2/4 h-screen relative items-center justify-center overflow-hidden">
            <img src="{{ asset('logo/foto_tm.jpg') }}" alt="Foto" class="h-full w-full object-cover">
            
            <!-- Overlay Gradient untuk membuat text lebih terbaca -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
            
            <!-- Copyright Section - Kanan Bawah (Di atas gambar) -->
            <div class="absolute bottom-0 left-0 right-0 p-12">
                <div class="text-center text-white items-center flex justify-center">
                        <p class="text-xs text-zinc-400 mt-1">
                            Powered by EduTM Technology
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    @fluxScripts
</body>
</html>