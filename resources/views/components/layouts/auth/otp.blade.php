    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>

    <body class="bg-white dark:bg-zinc-800 antialiased dark:bg-gradient-to-b dark:from-neutral-950 dark:to-neutral-900">

        <div class="min-h-screen flex items-center py-8 lg:py-12 px-4 sm:px-8 md:px-12 lg:px-16">
            
            <div class="w-full">
                <div class="gap-6 rounded-lg p-6 ">
                    {{ $slot }}
                </div>

              
            </div>
            
        </div>

        @fluxScripts
    </body>
    </html>