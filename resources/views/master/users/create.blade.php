<x-layouts.app :title="__('Master Admin Dashboard')">
<form action="{{ route('master.users.store') }}" method="POST" class="space-y-4">
    @csrf

    {{-- Pilih Pengguna --}}
    <div>
        <label>Pengguna</label>
        <select name="pengguna_id" required class="border rounded p-2 w-full">
            <option value="">-- Pilih Pengguna --</option>
            @foreach ($penggunas as $p)
                <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->email }})</option>
            @endforeach
        </select>
    </div>

    {{-- Pilih Role --}}
    <div>
        <label>Role</label>
        <select name="role" required class="border rounded p-2 w-full">
            @foreach ($roles as $role)
                <option value="{{ $role }}">{{ $role }}</option>

            @endforeach
        </select>
    </div>

    {{-- Password --}}
    <div>
        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required class="border rounded p-2 w-full">
    </div>

    <div>
        <label>Konfirmasi Password</label>
        <input type="password" name="password_confirmation" placeholder="Konfirmasi Password" required class="border rounded p-2 w-full">
    </div>

    {{-- Tombol --}}
    <div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Buat Akun</button>
    </div>
</form>
</x-layouts.app>
