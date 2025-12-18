<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pengguna;

class DummyPenggunaSeeder extends Seeder
{
    public function run(): void
    {
        $penggunaData = [
            [
                'nama' => 'Dr. Ahmad Hidayat',
                'email' => 'ahmad.hidayat@smktm.sch.id',
                'nik' => '3276010101800001',
                'status' => 'aktif'
            ],
            [
                'nama' => 'Siti Nurjanah, S.Pd',
                'email' => 'siti.nurjanah@smktm.sch.id',
                'nik' => '3276010202850002',
                'status' => 'aktif'
            ],
            [
                'nama' => 'Budi Santoso, M.Kom',
                'email' => 'budi.santoso@smktm.sch.id',
                'nik' => '3276010303900003',
                'status' => 'aktif'
            ],
            [
                'nama' => 'Rina Kartika, S.T',
                'email' => 'rina.kartika@smktm.sch.id',
                'nik' => '3276010404880004',
                'status' => 'aktif'
            ],
            [
                'nama' => 'Dedi Kurniawan, S.Kom',
                'email' => 'dedi.kurniawan@smktm.sch.id',
                'nik' => '3276010505920005',
                'status' => 'aktif'
            ]
        ];

        foreach ($penggunaData as $data) {
            Pengguna::firstOrCreate(
                ['email' => $data['email']],
                $data
            );
        }

        $this->command->info('✅ 5 dummy pengguna berhasil dibuat!');
    }
}
