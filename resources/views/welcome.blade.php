<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>EDU TM</title>
        <link rel="icon" href="logo/favicon.png">

        @vite(['resources/css/app.css', 'resources/js/app.js'])


        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"
                        >
                            Log in
                        </a>

                        @if (Route('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>
        <div class="flex w-screen h-screen gap-4 items-center justify-center">
    <h1 class="font-extrabold leading-none bg-clip-text text-transparent bg-center bg-cover bg-no-repeat"
        style="font-size: 18rem; background-image: url('{{ asset('img/img1.jpg') }}');">
        E
    </h1>
    <h1 class="font-extrabold leading-none bg-clip-text text-transparent bg-center bg-cover bg-no-repeat"
        style="font-size: 18rem; background-image: url('{{ asset('img/img2.jpg') }}');">
        D
    </h1>
    <h1 class="font-extrabold leading-none bg-clip-text text-transparent bg-center bg-cover bg-no-repeat"
        style="font-size: 18rem; background-image: url('{{ asset('img/img3.jpg') }}');">
        U
    </h1>
    <h1 class="font-extrabold leading-none bg-clip-text text-transparent bg-center bg-cover bg-no-repeat"
        style="font-size: 18rem; background-image: url('{{ asset('img/img4.jpg') }}');">
        T
    </h1>
    <h1 class="font-extrabold leading-none bg-clip-text text-transparent bg-center bg-cover bg-no-repeat"
        style="font-size: 18rem; background-image: url('{{ asset('img/img5.jpg') }}');">
        M
    </h1>
</div>

        </div>


        @if (Route('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>
</html>
