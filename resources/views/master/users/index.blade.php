<x-layouts.app :title="__('Master Admin Dashboard')">
    <div class="p-6">
        <div class="flex justify-end mb-4">
            <a href="{{ route('master.users.create') }}" class="bg-blue-600 p-2 rounded-md text-white">
                Tambah Akun Pengguna
            </a>
        </div>

        <h1 class="text-xl font-bold mb-4">Daftar Akun Pengguna</h1>

        @if ($users->isEmpty())
            <p>Belum ada user yang dibuat.</p>
        @else
            <table class="min-w-full text-sm text-left">
                <thead class="border-b border-gray-200 bg-gray-50 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="py-3 px-4 font-semibold">Nama</th>
                        <th class="py-3 px-4 font-semibold">Email</th>
                        <th class="py-3 px-4 font-semibold">Role</th>
                        <th class="py-3 px-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="py-3 px-4">{{ $user->name }}</td>
                            <td class="py-3 px-4">{{ $user->email }}</td>
                            <td class="py-3 px-4">{{ $user->role }}</td>
                            <td class="py-3 px-4 flex items-center gap-2">

                                <!-- Tombol Edit -->
                                <a href="{{ route('master.users.edit', $user->id) }}"
                                   class="bg-yellow-500 text-white py-1 px-3 rounded-md text-xs">
                                    Edit
                                </a>

                                <!-- Tombol Hapus -->
                                <form action="{{ route('master.users.destroy', $user->id) }}" method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-600 text-white py-1 px-3 rounded-md text-xs">
                                        Hapus
                                    </button>
                                </form>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif

    </div>
</x-layouts.app>
