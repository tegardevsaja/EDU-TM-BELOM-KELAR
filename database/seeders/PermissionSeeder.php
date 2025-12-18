<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'menu.dashboard',
            'dashboard.view',
            'dashboard.create',
            'dashboard.update',
            'dashboard.delete',
            'dashboard.import',
            'dashboard.export',
            'dashboard.template',

            'menu.pengguna',
            'pengguna.view',
            'pengguna.create',
            'pengguna.update',
            'pengguna.delete',
            'pengguna.import',
            'pengguna.export',
            'pengguna.template',

            'menu.users',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.import',
            'users.export',
            'users.template',

            'menu.siswa',
            'siswa.view',
            'siswa.create',
            'siswa.update',
            'siswa.delete',
            'siswa.import',
            'siswa.export',
            'siswa.template',

            'menu.kelas',
            'kelas.view',
            'kelas.create',
            'kelas.update',
            'kelas.delete',
            'kelas.import',
            'kelas.export',
            'kelas.template',

            'menu.jurusan',
            'jurusan.view',
            'jurusan.create',
            'jurusan.update',
            'jurusan.delete',
            'jurusan.import',
            'jurusan.export',
            'jurusan.template',

            'menu.tahunAjaran',
            'tahunAjaran.view',
            'tahunAjaran.create',
            'tahunAjaran.update',
            'tahunAjaran.delete',
            'tahunAjaran.import',
            'tahunAjaran.export',
            'tahunAjaran.template',

            'menu.penilaian',
            'penilaian.view',
            'penilaian.create',
            'penilaian.update',
            'penilaian.delete',
            'penilaian.import',
            'penilaian.export',
            'penilaian.template',

            'menu.nilai',
            'nilai.view',
            'nilai.create',
            'nilai.update',
            'nilai.delete',
            'nilai.import',
            'nilai.export',
            'nilai.template',

            'menu.sertifikat',
            'sertifikat.view',
            'sertifikat.create',
            'sertifikat.update',
            'sertifikat.delete',
            'sertifikat.import',
            'sertifikat.export',
            'sertifikat.template',

            'menu.sertifikat_template',
            'sertifikat_template.view',
            'sertifikat_template.create',
            'sertifikat_template.update',
            'sertifikat_template.delete',
            'sertifikat_template.import',
            'sertifikat_template.export',
            'sertifikat_template.template',

            'menu.absensi',
            'absensi.view',
            'absensi.create',
            'absensi.update',
            'absensi.delete',
            'absensi.import',
            'absensi.export',
            'absensi.template',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }
    }
}
