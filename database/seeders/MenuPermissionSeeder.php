<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MenuPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'dashboard',
            'pengguna',
            'users',
            'siswa',
            'kelas',
            'jurusan',
            'tahunAjaran',
            'penilaian',
            'nilai',
            'absensi',
            'sertifikat',
            'sertifikat_template',
        ];

        $actions = ['view', 'create', 'update', 'delete', 'import', 'export', 'template'];

        $all = [];

        foreach ($modules as $m) {
            $all[] = "menu.$m";
            foreach ($actions as $a) {
                $all[] = "$m.$a";
            }
        }

        // Tambahkan permission khusus yang tidak generik pada daftar
        $all[] = 'absensi.lock';

        foreach (array_unique($all) as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $master = Role::firstOrCreate(['name' => 'master_admin', 'guard_name' => 'web']);
        $master->givePermissionTo($all);

        // Grant Absensi menu + CRUD perms to admin and guru roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $guru  = Role::firstOrCreate(['name' => 'guru', 'guard_name' => 'web']);

        $absensiPerms = [
            'menu.absensi',
            'absensi.view',
            'absensi.create',
            'absensi.update',
            'absensi.delete',
            'absensi.import',
            'absensi.export',
            'absensi.template',
            'absensi.lock',
        ];

        $admin->givePermissionTo($absensiPerms);
        $guru->givePermissionTo($absensiPerms);
    }
}
