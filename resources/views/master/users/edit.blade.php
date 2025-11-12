<x-layouts.app :title="__('Master Admin Dashboard')">
 <form action="{{ route('master.users.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <!-- Nama -->
    <div class="mb-3">
        <label for="name" class="form-label">Nama</label>
        <input type="text" name="name" id="name"
               class="form-control"
               value="{{ old('name', $user->name) }}" required>
    </div>

    <!-- Email -->
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email"
               class="form-control"
               value="{{ old('email', $user->email) }}" required>
    </div>

    <!-- Role -->
    <div class="mb-3">
        <label for="role" class="form-label">Role</label>
        <select name="role" id="role" class="form-control" required>
            @foreach($roles as $role)
                <option value="{{ $role }}" {{ old('role', ($user->role->value ?? $user->role)) === $role ? 'selected' : '' }}>
                    {{ $role }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Password Baru (Opsional) -->
    <div class="mb-3">
        <label for="password" class="form-label">Password Baru (opsional)</label>
        <input type="password" name="password" id="password"
               class="form-control"
               placeholder="Biarkan kosong jika tidak ingin mengubah">
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
</form>

</x-layouts.app>
