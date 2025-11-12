<div wire:poll.5s="refreshFeed" class="mb-4">
    <div class="border rounded-lg bg-white dark:bg-zinc-800 shadow-sm">
        <div class="px-4 py-2 border-b flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="font-semibold">Aktivitas Terbaru</span>
                <span class="text-xs text-gray-500">(admin & guru)</span>
            </div>
            <button wire:click="refreshFeed" class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-zinc-700 hover:bg-gray-200">Refresh</button>
        </div>
        <div class="max-h-64 overflow-auto divide-y">
            @forelse($items as $it)
                <div class="px-4 py-3 text-sm flex items-start justify-between">
                    <div>
                        <div class="font-medium">{{ $it['user'] }} <span class="text-xs text-gray-500">({{ $it['role'] ?? '-' }})</span></div>
                        <div class="text-gray-700 dark:text-gray-300">{{ $it['method'] }} — {{ $it['action'] }}</div>
                    </div>
                    <div class="text-xs text-gray-500 whitespace-nowrap">{{ $it['time'] }}</div>
                </div>
            @empty
                <div class="px-4 py-6 text-center text-sm text-gray-500">Belum ada aktivitas.</div>
            @endforelse
        </div>
    </div>
</div>
