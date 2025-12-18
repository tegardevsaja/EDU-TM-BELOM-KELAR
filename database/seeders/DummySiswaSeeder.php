<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;

class DummySiswaSeeder extends Seeder
{
    public function run(): void
    {
        // Create jurusan if not exists
        $multimedia = Jurusan::firstOrCreate(
            ['nama_jurusan' => 'Multimedia'],
            ['created_at' => now(), 'updated_at' => now()]
        );
        
        $rpl = Jurusan::firstOrCreate(
            ['nama_jurusan' => 'Rekayasa Perangkat Lunak'],
            ['created_at' => now(), 'updated_at' => now()]
        );

        // Create kelas if not exists
        $kelasMM = Kelas::firstOrCreate(
            ['nama_kelas' => 'XII MM 1'],
            ['jurusan_id' => $multimedia->id, 'created_at' => now(), 'updated_at' => now()]
        );
        
        $kelasRPL = Kelas::firstOrCreate(
            ['nama_kelas' => 'XII RPL 1'],
            ['jurusan_id' => $rpl->id, 'created_at' => now(), 'updated_at' => now()]
        );

        // Dummy siswa data dengan semua field required
        $siswaData = [
            ['nama' => 'Adhaf Dewo Wicaksono', 'nis' => '22100001', 'nisn' => '0012345678', 'kelas_id' => $kelasMM->id, 'jenis_kelamin' => 'L', 'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '2007-01-15', 'agama' => 'Islam', 'nama_orang_tua' => 'Budi Wicaksono', 'alamat_orang_tua' => 'Jl. Melati No. 1', 'no_hp_orang_tua' => '081234567001', 'asal_sekolah' => 'SMPN 1 Jakarta'],
            ['nama' => 'Siti Nurhaliza Putri', 'nis' => '22100002', 'nisn' => '0012345679', 'kelas_id' => $kelasMM->id, 'jenis_kelamin' => 'P', 'tempat_lahir' => 'Depok', 'tanggal_lahir' => '2007-02-20', 'agama' => 'Islam', 'nama_orang_tua' => 'Ahmad Putri', 'alamat_orang_tua' => 'Jl. Melur No. 2', 'no_hp_orang_tua' => '081234567002', 'asal_sekolah' => 'SMPN 2 Depok'],
            ['nama' => 'Muhammad Rizki Pratama', 'nis' => '22100003', 'nisn' => '0012345680', 'kelas_id' => $kelasRPL->id, 'jenis_kelamin' => 'L', 'tempat_lahir' => 'Bogor', 'tanggal_lahir' => '2007-03-10', 'agama' => 'Islam', 'nama_orang_tua' => 'Rizki Pratama Sr', 'alamat_orang_tua' => 'Jl. Kenanga No. 3', 'no_hp_orang_tua' => '081234567003', 'asal_sekolah' => 'SMPN 3 Bogor'],
            ['nama' => 'Dewi Sartika Maharani', 'nis' => '22100004', 'nisn' => '0012345681', 'kelas_id' => $kelasMM->id, 'jenis_kelamin' => 'P', 'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '2007-04-05', 'agama' => 'Islam', 'nama_orang_tua' => 'Sartika Maharani', 'alamat_orang_tua' => 'Jl. Mawar No. 4', 'no_hp_orang_tua' => '081234567004', 'asal_sekolah' => 'SMPN 4 Jakarta'],
            ['nama' => 'Ahmad Fauzi Rahman', 'nis' => '22100005', 'nisn' => '0012345682', 'kelas_id' => $kelasRPL->id, 'jenis_kelamin' => 'L', 'tempat_lahir' => 'Tangerang', 'tanggal_lahir' => '2007-05-12', 'agama' => 'Islam', 'nama_orang_tua' => 'Fauzi Rahman', 'alamat_orang_tua' => 'Jl. Anggrek No. 5', 'no_hp_orang_tua' => '081234567005', 'asal_sekolah' => 'SMPN 5 Tangerang'],
            ['nama' => 'Rina Kartika Sari', 'nis' => '22100006', 'nisn' => '0012345683', 'kelas_id' => $kelasMM->id, 'jenis_kelamin' => 'P', 'tempat_lahir' => 'Bekasi', 'tanggal_lahir' => '2007-06-18', 'agama' => 'Islam', 'nama_orang_tua' => 'Kartika Sari', 'alamat_orang_tua' => 'Jl. Dahlia No. 6', 'no_hp_orang_tua' => '081234567006', 'asal_sekolah' => 'SMPN 6 Bekasi'],
            ['nama' => 'Bayu Setiawan Putra', 'nis' => '22100007', 'nisn' => '0012345684', 'kelas_id' => $kelasRPL->id, 'jenis_kelamin' => 'L', 'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '2007-07-22', 'agama' => 'Islam', 'nama_orang_tua' => 'Setiawan Putra', 'alamat_orang_tua' => 'Jl. Tulip No. 7', 'no_hp_orang_tua' => '081234567007', 'asal_sekolah' => 'SMPN 7 Jakarta'],
            ['nama' => 'Indira Safitri Lestari', 'nis' => '22100008', 'nisn' => '0012345685', 'kelas_id' => $kelasMM->id, 'jenis_kelamin' => 'P', 'tempat_lahir' => 'Depok', 'tanggal_lahir' => '2007-08-14', 'agama' => 'Islam', 'nama_orang_tua' => 'Safitri Lestari', 'alamat_orang_tua' => 'Jl. Kamboja No. 8', 'no_hp_orang_tua' => '081234567008', 'asal_sekolah' => 'SMPN 8 Depok'],
            ['nama' => 'Dimas Arya Wijaya', 'nis' => '22100009', 'nisn' => '0012345686', 'kelas_id' => $kelasRPL->id, 'jenis_kelamin' => 'L', 'tempat_lahir' => 'Bogor', 'tanggal_lahir' => '2007-09-03', 'agama' => 'Islam', 'nama_orang_tua' => 'Arya Wijaya', 'alamat_orang_tua' => 'Jl. Sakura No. 9', 'no_hp_orang_tua' => '081234567009', 'asal_sekolah' => 'SMPN 9 Bogor'],
            ['nama' => 'Putri Amelia Zahara', 'nis' => '22100010', 'nisn' => '0012345687', 'kelas_id' => $kelasMM->id, 'jenis_kelamin' => 'P', 'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '2007-10-25', 'agama' => 'Islam', 'nama_orang_tua' => 'Amelia Zahara', 'alamat_orang_tua' => 'Jl. Lily No. 10', 'no_hp_orang_tua' => '081234567010', 'asal_sekolah' => 'SMPN 10 Jakarta'],
            ['nama' => 'Reza Firmansyah', 'nis' => '22100011', 'nisn' => '0012345688', 'kelas_id' => $kelasRPL->id, 'jenis_kelamin' => 'L', 'tempat_lahir' => 'Tangerang', 'tanggal_lahir' => '2007-11-08', 'agama' => 'Islam', 'nama_orang_tua' => 'Firmansyah', 'alamat_orang_tua' => 'Jl. Jasmine No. 11', 'no_hp_orang_tua' => '081234567011', 'asal_sekolah' => 'SMPN 11 Tangerang'],
            ['nama' => 'Aulia Rahma Fitri', 'nis' => '22100012', 'nisn' => '0012345689', 'kelas_id' => $kelasMM->id, 'jenis_kelamin' => 'P', 'tempat_lahir' => 'Bekasi', 'tanggal_lahir' => '2007-12-16', 'agama' => 'Islam', 'nama_orang_tua' => 'Rahma Fitri', 'alamat_orang_tua' => 'Jl. Lavender No. 12', 'no_hp_orang_tua' => '081234567012', 'asal_sekolah' => 'SMPN 12 Bekasi'],
            ['nama' => 'Fajar Nugraha Putra', 'nis' => '22100013', 'nisn' => '0012345690', 'kelas_id' => $kelasRPL->id, 'jenis_kelamin' => 'L', 'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '2006-01-30', 'agama' => 'Islam', 'nama_orang_tua' => 'Nugraha Putra', 'alamat_orang_tua' => 'Jl. Sunflower No. 13', 'no_hp_orang_tua' => '081234567013', 'asal_sekolah' => 'SMPN 13 Jakarta'],
            ['nama' => 'Nadia Permata Sari', 'nis' => '22100014', 'nisn' => '0012345691', 'kelas_id' => $kelasMM->id, 'jenis_kelamin' => 'P', 'tempat_lahir' => 'Depok', 'tanggal_lahir' => '2006-02-14', 'agama' => 'Islam', 'nama_orang_tua' => 'Permata Sari', 'alamat_orang_tua' => 'Jl. Violet No. 14', 'no_hp_orang_tua' => '081234567014', 'asal_sekolah' => 'SMPN 14 Depok'],
            ['nama' => 'Arief Budiman', 'nis' => '22100015', 'nisn' => '0012345692', 'kelas_id' => $kelasRPL->id, 'jenis_kelamin' => 'L', 'tempat_lahir' => 'Bogor', 'tanggal_lahir' => '2006-03-28', 'agama' => 'Islam', 'nama_orang_tua' => 'Budiman', 'alamat_orang_tua' => 'Jl. Carnation No. 15', 'no_hp_orang_tua' => '081234567015', 'asal_sekolah' => 'SMPN 15 Bogor'],
            ['nama' => 'Sari Wulandari', 'nis' => '22100016', 'nisn' => '0012345693', 'kelas_id' => $kelasMM->id, 'jenis_kelamin' => 'P', 'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '2006-04-11', 'agama' => 'Islam', 'nama_orang_tua' => 'Wulandari', 'alamat_orang_tua' => 'Jl. Peony No. 16', 'no_hp_orang_tua' => '081234567016', 'asal_sekolah' => 'SMPN 16 Jakarta'],
            ['nama' => 'Yoga Pratama Putra', 'nis' => '22100017', 'nisn' => '0012345694', 'kelas_id' => $kelasRPL->id, 'jenis_kelamin' => 'L', 'tempat_lahir' => 'Tangerang', 'tanggal_lahir' => '2006-05-07', 'agama' => 'Islam', 'nama_orang_tua' => 'Pratama Putra', 'alamat_orang_tua' => 'Jl. Iris No. 17', 'no_hp_orang_tua' => '081234567017', 'asal_sekolah' => 'SMPN 17 Tangerang'],
            ['nama' => 'Maya Anggraini', 'nis' => '22100018', 'nisn' => '0012345695', 'kelas_id' => $kelasMM->id, 'jenis_kelamin' => 'P', 'tempat_lahir' => 'Bekasi', 'tanggal_lahir' => '2006-06-19', 'agama' => 'Islam', 'nama_orang_tua' => 'Anggraini', 'alamat_orang_tua' => 'Jl. Daffodil No. 18', 'no_hp_orang_tua' => '081234567018', 'asal_sekolah' => 'SMPN 18 Bekasi'],
            ['nama' => 'Hendra Saputra', 'nis' => '22100019', 'nisn' => '0012345696', 'kelas_id' => $kelasRPL->id, 'jenis_kelamin' => 'L', 'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '2006-07-23', 'agama' => 'Islam', 'nama_orang_tua' => 'Saputra', 'alamat_orang_tua' => 'Jl. Poppy No. 19', 'no_hp_orang_tua' => '081234567019', 'asal_sekolah' => 'SMPN 19 Jakarta'],
            ['nama' => 'Lestari Dewi Putri', 'nis' => '22100020', 'nisn' => '0012345697', 'kelas_id' => $kelasMM->id, 'jenis_kelamin' => 'P', 'tempat_lahir' => 'Depok', 'tanggal_lahir' => '2006-08-15', 'agama' => 'Islam', 'nama_orang_tua' => 'Dewi Putri', 'alamat_orang_tua' => 'Jl. Hibiscus No. 20', 'no_hp_orang_tua' => '081234567020', 'asal_sekolah' => 'SMPN 20 Depok'],
        ];

        foreach ($siswaData as $data) {
            Siswa::firstOrCreate(
                ['nis' => $data['nis']],
                $data
            );
        }

        $this->command->info('✅ 20 dummy siswa berhasil dibuat!');
        $this->command->info('📊 Jurusan: Multimedia (' . $kelasMM->siswas()->count() . ' siswa), RPL (' . $kelasRPL->siswas()->count() . ' siswa)');
    }
}

