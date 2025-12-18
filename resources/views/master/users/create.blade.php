<x-layouts.app :title="__('Buat Akun Pengguna')">
    <div class="p-6">
        <div class="mx-auto max-w-3xl overflow-hidden rounded-xl border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200/70 px-6 py-4 dark:border-zinc-800">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Buat Akun Pengguna</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Hubungkan akun login dengan data pengguna dan atur role akses.</p>
            </div>

            {{-- Error Messages --}}
            @if($errors->any())
                <div class="mx-6 mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                    <h4 class="font-medium text-red-900 dark:text-red-100 mb-2">Terjadi kesalahan:</h4>
                    <ul class="text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('master.users.store') }}" method="POST" class="space-y-5 px-6 py-5">
                @csrf

                {{-- Pilih Pengguna --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">Pengguna</label>
                        <span class="text-[11px] uppercase tracking-wide text-red-500 font-medium">Wajib</span>
                    </div>
                    <select name="pengguna_id" required class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="">-- Pilih Pengguna --</option>
                        @foreach ($penggunas as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->email }})</option>
                        @endforeach
                    </select>
                    @error('pengguna_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pilih Role --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">Role</label>
                        <span class="text-[11px] uppercase tracking-wide text-red-500 font-medium">Wajib</span>
                    </div>
                    <select name="role" required class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        @foreach ($roles as $role)
                            <option value="{{ $role }}">{{ $role }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-zinc-800 dark:text-zinc-100">Password</label>
                        <span class="text-[11px] uppercase tracking-wide text-red-500 font-medium">Wajib</span>
                    </div>
                    <div class="relative">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Minimal 8 karakter"
                            required
                            class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 pr-10 text-sm text-zinc-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300"
                            onclick="togglePasswordVisibility('password', this)"
                        >
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                <circle cx="12" cy="12" r="3" stroke-width="1.8" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Konfirmasi Password</label>
                    <div class="relative">
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            placeholder="Ulangi password"
                            required
                            class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 pr-10 text-sm text-zinc-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300"
                            onclick="togglePasswordVisibility('password_confirmation', this)"
                        >
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                <circle cx="12" cy="12" r="3" stroke-width="1.8" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="flex justify-end gap-2 border-t border-zinc-200/70 bg-zinc-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <a href="{{ route('master.users') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Batal</a>
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Buat Akun</button>
                </div>

            </form>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, buttonEl) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            // Optional: you could swap icon here if desired.
        }
    </script>
</x-layouts.app>
