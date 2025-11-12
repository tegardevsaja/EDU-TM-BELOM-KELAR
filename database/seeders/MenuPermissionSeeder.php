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

        foreach (array_unique($all) as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $master = Role::firstOrCreate(['name' => 'master_admin']);
        $master->givePermissionTo($all);
    }
}
