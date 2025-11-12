<x-layouts.app :title="__('Buat Akun Pengguna')">
    <div class="p-6">
        <div class="mx-auto max-w-2xl overflow-hidden rounded-lg border bg-white dark:bg-zinc-800">
            <div class="border-b px-6 py-4">
                <h2 class="text-lg font-semibold">Buat Akun Pengguna</h2>
            </div>

            <form action="{{ route('master.users.store') }}" method="POST" class="space-y-5 p-6">
                @csrf

                {{-- Pilih Pengguna --}}
                <div>
                    <label class="mb-1 block text-sm font-medium">Pengguna</label>
                    <select name="pengguna_id" required class="w-full rounded border px-3 py-2">
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
                    <label class="mb-1 block text-sm font-medium">Role</label>
                    <select name="role" required class="w-full rounded border px-3 py-2">
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
                    <label class="mb-1 block text-sm font-medium">Password</label>
                    <input type="password" name="password" placeholder="Password" required class="w-full rounded border px-3 py-2">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" placeholder="Konfirmasi Password" required class="w-full rounded border px-3 py-2">
                </div>

                {{-- Tombol --}}
                <div class="border-t bg-zinc-50 p-4 text-right dark:bg-zinc-900">
                    <a href="{{ route('master.users') }}" class="rounded border px-4 py-2">Batal</a>
                    <button type="submit" class="ml-2 rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Buat Akun</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
