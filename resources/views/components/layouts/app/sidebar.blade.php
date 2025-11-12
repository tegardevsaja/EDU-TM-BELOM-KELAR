    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
        <head>
            @include('partials.head')
            <!-- <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png"> -->
            <link rel="icon" type="image/x-icon" href="{{ asset('logo/favicon.png') }}">
            <link rel="icon" href="logo/favicon.png">
                <meta name="csrf-token" content="{{ csrf_token() }}">

                @livewireStyles
        </head>
        <body class="min-h-screen bg-white dark:bg-zinc-800">
            @auth
            <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

                @php
                    $dashUrl = route('dashboard');
                    $routePrefix = 'master'; // default
                    
                    if (auth()->check() && method_exists(auth()->user(), 'hasRole')) {
                        if (auth()->user()->hasRole('master_admin')) {
                            $dashUrl = route('master.dashboard');
                            $routePrefix = 'master';
                        } elseif (auth()->user()->hasRole('admin')) {
                            $dashUrl = route('admin.dashboard');
                            $routePrefix = 'admin';
                        } elseif (auth()->user()->hasRole('guru')) {
                            $dashUrl = route('guru.dashboard');
                            $routePrefix = 'guru';
                        }
                    }
                @endphp
                <a href="{{ $dashUrl }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                    <x-app-logo />
                </a>

                <flux:navlist variant="outline">
                    <flux:navlist.group :heading="__('Dashboard')" class="grid">
                        <flux:navlist.item icon="home" href="{{ $dashUrl }}" :current="request()->routeIs('master.dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('guru.dashboard') || request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                        @can('menu.pengguna')
                        <flux:navlist.item icon="users" href="{{ route($routePrefix . '.pengguna') }}" :current="request()->routeIs('pengguna')" wire:navigate>{{ __('Data pengguna') }}</flux:navlist.item>
                        @endcan
                        @can('menu.users')
                        <flux:navlist.item icon="user" href="{{ route($routePrefix . '.users') }}" :current="request()->routeIs('users')" wire:navigate>{{ __('Acount pengguna') }}</flux:navlist.item>
                        @endcan
                    </flux:navlist.group>
                </flux:navlist>

                <flux:navlist variant="outline">
                    <flux:navlist.group :heading="__('Siswa')" class="grid">
                        @can('menu.siswa')
                        <flux:navlist.item icon="academic-cap" href="{{ route($routePrefix . '.siswa.index') }}" :current="request()->routeIs('siswa')" wire:navigate>{{ __('Data Siswa') }}</flux:navlist.item>
                        @endcan
                        @can('menu.kelas')
                        <flux:navlist.item icon="rectangle-stack" href="{{ route($routePrefix . '.kelas') }}" :current="request()->routeIs('kelas')" wire:navigate>{{ __('Kelas') }}</flux:navlist.item>
                        @endcan
                        @can('menu.jurusan')
                        <flux:navlist.item icon="squares-2x2" href="{{ route($routePrefix . '.jurusan') }}" :current="request()->routeIs('jurusan')" wire:navigate>{{ __('Jurusan') }}</flux:navlist.item>
                        @endcan
                    </flux:navlist>
                </flux:navlist>
                <flux:navlist variant="outline">
                    <flux:navlist.group :heading="__('Tahun Ajaran')" class="grid">
                        @can('menu.tahunAjaran')
                        <flux:navlist.item icon="calendar" href="{{ route($routePrefix . '.tahunAjaran') }}" :current="request()->routeIs('tahunAjaran')" wire:navigate>{{ __('Tahun ajaran') }}</flux:navlist.item>
                        @endcan
                    </flux:navlist>
                </flux:navlist>
                <flux:navlist variant="outline">
                    <flux:navlist.group :heading="__('Kehadiran')" class="grid">
                        @can('menu.absensi')
                        <flux:navlist.item icon="check-badge" href="{{ route($routePrefix . '.absensi') }}" :current="request()->routeIs('absensi')" wire:navigate>{{ __('Absensi') }}</flux:navlist.item>
                        @endcan
                    </flux:navlist>
                </flux:navlist>
                <flux:navlist variant="outline">
                    <flux:navlist.group :heading="__('Penilaian')" class="grid">
                        @can('menu.penilaian')
                        <flux:navlist.item icon="clipboard-document-list" href="{{ route($routePrefix . '.penilaian') }}" :current="request()->routeIs('penilaian')" wire:navigate>{{ __('Template Nilai') }}</flux:navlist.item>
                        @endcan
                        @can('menu.nilai')
                        <flux:navlist.item icon="chart-bar" href="{{ route($routePrefix . '.nilai.index') }}" :current="request()->routeIs('penilaian')" wire:navigate>{{ __('Nilai') }}</flux:navlist.item>
                        @endcan
                    </flux:navlist>
                </flux:navlist>
                @can('menu.sertifikat')
                <flux:navlist variant="outline">
                    <flux:navlist.group :heading="__('Sertifikat')" class="grid">
                        <flux:navlist.item icon="document-check" href="{{ route($routePrefix . '.sertifikat.index') }}" :current="request()->routeIs('sertifikat')" wire:navigate>{{ __('Sertifikat') }}</flux:navlist.item>
                    </flux:navlist>
                </flux:navlist>
                @endcan

                @role('master_admin')
                <flux:navlist variant="outline">
                    <flux:navlist.group :heading="__('Pengaturan')" class="grid">
                        <flux:navlist.item icon="shield-check" href="{{ route('master.permissions.index') }}" :current="request()->routeIs('master.permissions.*')" wire:navigate>{{ __('Permissions') }}</flux:navlist.item>
                    </flux:navlist.group>
                </flux:navlist>
                @endrole

                <flux:spacer />

                <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevrons-up-down"
                    />

                    <flux:menu class="w-[220px]">
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item href="{{ route('settings.profile') }}" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>

            </flux:sidebar>

            <!-- Mobile User Menu -->
            <flux:header class="lg:hidden">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

                <flux:spacer />

                <flux:dropdown position="top" align="end">
                    <flux:profile
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item href="{{ route('settings.profile') }}" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </flux:header>
            @endauth

            {{ $slot }}

                @livewireScripts

            @fluxScripts
        </body>
    </html>