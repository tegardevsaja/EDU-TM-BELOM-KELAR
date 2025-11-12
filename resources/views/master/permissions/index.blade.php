<x-layouts.app :title="__('Role Menu Permissions')">
    <div class="p-6">
    @if(session('success'))
        <div class="mb-4 text-green-700 bg-green-100 p-3 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 text-red-700 bg-red-100 p-3 rounded">{{ session('error') }}</div>
    @endif

    <h1 class="text-xl font-semibold mb-4">Role Menu & Feature Permissions</h1>

    @php
        // Build grouping for UI
        $allPerms = collect($permissions)->pluck('name')->all();
        $menuPerms = collect($permissions)->filter(fn($p) => str_starts_with($p->name, 'menu.'))->pluck('name')->all();
        $featurePerms = collect($permissions)->reject(fn($p) => str_starts_with($p->name, 'menu.'))->pluck('name')->all();

        // Derive modules list from menu.* and feature perms
        $modulesFromMenu = collect($menuPerms)->map(fn($n) => explode('.', $n)[1] ?? null)->filter()->unique()->values();
        $modulesFromFeatures = collect($featurePerms)->map(fn($n) => explode('.', $n)[0])->unique()->values();
        $modules = $modulesFromMenu->merge($modulesFromFeatures)->unique()->values();

        // Group feature perms by module
        $byModule = [];
        foreach ($modules as $mod) {
            $byModule[$mod] = collect($featurePerms)->filter(fn($n) => str_starts_with($n, $mod.'.'))->values();
        }
    @endphp

    <div class="space-y-8">
        @foreach($roles as $role)
            <div class="border rounded p-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-lg">{{ ucfirst(str_replace('_',' ', $role->name)) }}</h2>
                    <div class="flex items-center gap-2">
                        @if($role->name === 'master_admin')
                            <span class="text-xs px-2 py-1 bg-zinc-200 rounded">Superuser (all access)</span>
                        @endif
                    </div>
                </div>

                @if($role->name !== 'master_admin')
                <form method="POST" action="{{ route('master.permissions.update', $role->name) }}" class="mt-4" x-data>
                    @csrf

                    <div class="mb-4 flex items-center gap-3">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" id="global-select-all-{{ $role->name }}" @change="document.querySelectorAll('[data-role={{ $role->name }}] input[type=checkbox]').forEach(cb=>cb.checked=$event.target.checked)">
                            <span class="text-sm font-medium">Pilih semua</span>
                        </label>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="rounded border p-3" data-role="{{ $role->name }}">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-semibold">Menu Visibility</h3>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" @change="
                                        $el.closest('[data-role]')
                                          .querySelectorAll('input[data-group=menu]')
                                          .forEach(cb => cb.checked = $event.target.checked)
                                    ">
                                    <span>Pilih semua menu</span>
                                </label>
                            </div>
                            <div class="grid sm:grid-cols-2 gap-2">
                                @foreach($modules as $mod)
                                    @php $permName = 'menu.'.$mod; @endphp
                                    @if(in_array($permName, $allPerms))
                                    <label class="flex items-center gap-2 p-2 border rounded">
                                        <input data-group="menu" type="checkbox" name="permissions[]" value="{{ $permName }}" {{ $role->hasPermissionTo($permName) ? 'checked' : '' }}>
                                        <span>{{ $permName }}</span>
                                    </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded border p-3" data-role="{{ $role->name }}">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-semibold">Feature Permissions</h3>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" @change="
                                        $el.closest('[data-role]')
                                          .querySelectorAll('input[data-group=features]')
                                          .forEach(cb => cb.checked = $event.target.checked)
                                    ">
                                    <span>Pilih semua fitur</span>
                                </label>
                            </div>
                            <div class="space-y-3">
                                @foreach($modules as $mod)
                                    @php $modulePerms = $byModule[$mod] ?? collect(); @endphp
                                    @if($modulePerms->isNotEmpty())
                                        <div class="rounded border p-2">
                                            <div class="flex items-center justify-between">
                                                <h4 class="font-medium">{{ ucfirst($mod) }}</h4>
                                                <label class="flex items-center gap-2 text-xs">
                                                    <input type="checkbox" @change="
                                                        $el.closest('div.rounded')
                                                          .querySelectorAll('input[data-module={{ $mod }}]')
                                                          .forEach(cb => cb.checked = $event.target.checked)
                                                    ">
                                                    <span>Pilih semua</span>
                                                </label>
                                            </div>
                                            <div class="mt-2 grid sm:grid-cols-2 gap-2">
                                                @foreach($modulePerms as $perm)
                                                    <label class="flex items-center gap-2 p-2 border rounded">
                                                        <input data-group="features" data-module="{{ $mod }}" type="checkbox" name="permissions[]" value="{{ $perm }}" {{ $role->hasPermissionTo($perm) ? 'checked' : '' }}>
                                                        <span>{{ $perm }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
                    </div>
                </form>
                @endif
            </div>
        @endforeach
    </div>
    </div>
</x-layouts.app>
