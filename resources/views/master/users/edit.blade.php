<x-layouts.app :title="__('Master Admin Dashboard')">
    <div class="p-6">
        <div class="mx-auto max-w-3xl overflow-hidden rounded-xl border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-200/70 px-6 py-4 dark:border-zinc-800">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Edit Akun Pengguna</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Perbarui identitas akun, role, dan password bila diperlukan.</p>
            </div>

            <form action="{{ route('master.users.update', $user->id) }}" method="POST" class="space-y-5 px-6 py-5">
                @csrf
                @method('PUT')

                <!-- Nama -->
                <div>
                    <div class="mb-1 flex items-center justify-between">
                        <label for="name" class="text-sm font-medium text-zinc-800 dark:text-zinc-100">Nama</label>
                        <span class="text-[11px] uppercase tracking-wide text-red-500 font-medium">Wajib</span>
                    </div>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        value="{{ old('name', $user->name) }}" required>
                </div>

                <!-- Email -->
                <div>
                    <div class="mb-1 flex items-center justify-between">
                        <label for="email" class="text-sm font-medium text-zinc-800 dark:text-zinc-100">Email</label>
                        <span class="text-[11px] uppercase tracking-wide text-red-500 font-medium">Wajib</span>
                    </div>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        value="{{ old('email', $user->email) }}" required>
                </div>

                <!-- Role -->
                <div>
                    <div class="mb-1 flex items-center justify-between">
                        <label for="role" class="text-sm font-medium text-zinc-800 dark:text-zinc-100">Role</label>
                        <span class="text-[11px] uppercase tracking-wide text-red-500 font-medium">Wajib</span>
                    </div>
                    <select
                        name="role"
                        id="role"
                        class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        required
                    >
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ old('role', ($user->role->value ?? $user->role)) === $role ? 'selected' : '' }}>
                                {{ $role }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Password Baru (Opsional) -->
                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Password Baru (opsional)</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 pr-10 text-sm text-zinc-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            placeholder="Biarkan kosong jika tidak ingin mengubah"
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
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-zinc-800 dark:text-zinc-100">Konfirmasi Password (opsional)</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 pr-10 text-sm text-zinc-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                            placeholder="Konfirmasi password baru"
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
                    @error('password_confirmation')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <a href="{{ route('master.users') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Batal</a>
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Simpan</button>
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
        }
    </script>

</x-layouts.app>
