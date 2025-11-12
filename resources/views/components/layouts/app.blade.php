    <x-layouts.app.sidebar :title="$title ?? null">
        @role('master_admin')
            <div class="p-3">
                <livewire:master-activity-feed />
            </div>
        @endrole
        <flux:main>
            {{ $slot }}
        </flux:main>
    </x-layouts.app.sidebar>
