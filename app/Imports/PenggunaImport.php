<?php

namespace App\Imports;

use App\Models\Pengguna;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PenggunaImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        
        return new Pengguna([
            'nama'  => $row['nama'],
            'email' => $row['email'],
            'nik'   => $row['nik'] ?? null,
        ]);
    }
}
