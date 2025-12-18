<x-layouts.app.admin.sidebar :title="$title ?? null">
    <flux:main>
        <div class="flex min-h-screen flex-col">
            <div class="flex-1">
                <x-top-controls />
                {{ $slot }}
            </div>
            <x-app-footer />
        </div>
    </flux:main>
</x-layouts.app.sidebar>
