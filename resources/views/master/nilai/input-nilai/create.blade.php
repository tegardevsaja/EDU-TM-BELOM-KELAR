<x-layouts.app :title="'Input Nilai'">
    <div class="p-6">

        <h1 class="text-xl font-bold mb-4">Input Nilai: {{ $template->nama_template }}</h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-2 rounded mb-4">{{ session('success') }}</div>
        @endif

        <form action="{{ route('penilaian.store', $template->id) }}" method="POST">
            @csrf

            <!-- Pilih siswa -->
            <div class="mb-4">
                <label class="block font-semibold mb-1">Pilih Siswa</label>
                <select name="siswa_id" class="border rounded p-2 w-full">
                    @foreach($siswa as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tabel nilai -->
            <div class="overflow-x-auto">
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border py-2 px-3">No</th>
                            <th class="border py-2 px-3">Komponen</th>
                            <th class="border py-2 px-3">Subfield</th>
                            <th class="border py-2 px-3">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach($template->komponen as $komponen => $subfields)
                            @foreach($subfields as $subfield)
                                <tr>
                                    <td class="border py-2 px-3">{{ $no++ }}</td>
                                    <td class="border py-2 px-3">{{ $komponen }}</td>
                                    <td class="border py-2 px-3">{{ $subfield }}</td>
                                    <td class="border py-2 px-3">
                                        <input type="number" name="nilai[{{ $komponen }}][{{ $subfield }}]"
                                               class="w-full border rounded p-1">
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">
                Simpan Nilai
            </button>
        </form>

    </div>
</x-layouts.app>
