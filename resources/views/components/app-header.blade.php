 @unless (request()->routeIs('settings*') || request()->routeIs('master.dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('guru.dashboard') || request()->routeIs('dashboard'))
<flux:header class="sticky top-0 z-40 border-b border-zinc-200/70 bg-white/70 backdrop-blur dark:border-zinc-800/70 dark:bg-zinc-900/60">
    <div class="flex w-full items-center gap-3 px-2">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <div class="flex-1"></div>
        @php
            try {
                $notifs = \App\Models\Activity::latest()->limit(10)->get();
            } catch (\Throwable $e) {
                $notifs = collect();
            }
        @endphp
        <flux:dropdown position="bottom" align="end">
            <button type="button" class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-600 hover:bg-zinc-50 dark:border-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-800">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                    <path d="M12 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 006 14h12a 1 1 0 00.707-1.707L18 11.586V8a6 6 0 00-6-6z"/>
                    <path d="M9 18a3 3 0 006 0H9z"/>
                </svg>
                <span class="absolute -top-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-rose-500"></span>
            </button>
            <flux:menu class="w-[320px] max-h-[70vh] overflow-auto">
                <div class="px-2 py-2 text-sm font-semibold text-zinc-700 dark:text-zinc-200">Notifikasi</div>
                <flux:menu.separator />
                @forelse($notifs as $a)
                    <div class="px-3 py-2 text-sm flex gap-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/80">
                        <div class="h-8 w-8 rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 flex items-center justify-center text-xs font-bold">! </div>
                        <div class="min-w-0">
                            <p class="text-zinc-900 dark:text-zinc-100 truncate">{{ $a->action ?? 'Aktivitas' }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $a->description ?? '-' }}</p>
                            <p class="text-[11px] text-zinc-400 dark:text-zinc-500">{{ optional($a->created_at)->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-3 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">Tidak ada notifikasi</div>
                @endforelse
                <flux:menu.separator />
                <div class="px-2 py-2">
                    <a href="#" class="w-full inline-flex justify-center rounded-lg border border-zinc-200 px-3 py-2 text-sm hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">Lihat semua</a>
                </div>
            </flux:menu>
        </flux:dropdown>
        <flux:dropdown position="bottom" align="end">
            <flux:profile
                :name="auth()->user()->name"
                :initials="auth()->user()->initials()"
                icon-trailing="chevron-down"
            />
            <flux:menu class="w-[220px]">
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                            <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                {{ auth()->user()->initials() }}
                            </span>
                        </span>
                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                    </div>
                </div>
                <flux:menu.separator />
                <flux:menu.item href="{{ route('settings.profile') }}" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </div>
</flux:header>
@endunless
