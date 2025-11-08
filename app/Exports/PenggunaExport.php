<?php

namespace App\Exports;

use App\Models\Pengguna;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PenggunaExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Ambil data pengguna (pilih kolom sesuai kebutuhan)
        return Pengguna::select('id', 'nama', 'email', 'nik', 'created_at')->get();
    }

    public function headings(): array
    {
        // Header kolom di Excel
        return [
            'ID',
            'Nama',
            'Email',
            'NIK',
            'Tanggal Dibuat'
        ];
    }
}
