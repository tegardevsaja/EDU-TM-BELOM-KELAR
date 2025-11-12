# 🎓 Sistem Otomatis Lulus/Alumni

## Fitur yang Telah Diimplementasikan

### 1. **Auto-Graduate Command**
- **Command**: `php artisan students:auto-graduate`
- **Dry Run**: `php artisan students:auto-graduate --dry-run`

### 2. **Logic Kelulusan Otomatis**
Siswa akan otomatis menjadi **Alumni** jika:

#### Kriteria A: Kelas XII (Tingkat Akhir)
- Status saat ini: `Aktif`
- Kelas mengandung "XII" (contoh: XII RPL, XII TKJ)
- Tahun ajaran sudah selesai (`tanggal_selesai < hari ini`)
- Tahun ajaran sudah tidak aktif (`aktif = false`)

#### Kriteria B: 3+ Tahun di Sistem
- Status saat ini: `Aktif`
- Sudah 3 tahun atau lebih sejak `tahun_masuk`
- Tahun ajaran sudah selesai

### 3. **Scheduling Otomatis**
Sistem akan berjalan otomatis:
- **Harian**: Setiap hari jam 02:00 (proses kelulusan)
- **Mingguan**: Setiap Senin jam 08:00 (dry-run untuk monitoring)

### 4. **Logging & Audit Trail**
- Log file: `storage/logs/auto-graduation.log`
- Preview log: `storage/logs/auto-graduation-preview.log`
- Laravel log: Detail setiap siswa yang lulus

## Cara Penggunaan

### Manual Testing
```bash
# Lihat preview tanpa mengubah data
php artisan students:auto-graduate --dry-run

# Jalankan kelulusan sesungguhnya
php artisan students:auto-graduate
```

### Setup Cron Job (Production)
Tambahkan ke crontab server:
```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### Monitoring
```bash
# Lihat log kelulusan
tail -f storage/logs/auto-graduation.log

# Lihat preview mingguan
tail -f storage/logs/auto-graduation-preview.log
```

## Keamanan & Kontrol

### ✅ **Safety Features**
- **Dry-run mode** untuk testing
- **Logging lengkap** setiap perubahan
- **Hanya proses tahun ajaran tidak aktif**
- **Tidak mengubah status Alumni/Nonaktif**

### ⚙️ **Konfigurasi**
Edit `app/Console/Kernel.php` untuk mengubah:
- Waktu eksekusi
- Frekuensi running
- Log file location

## Troubleshooting

### Command Tidak Jalan
```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Test manual
php artisan students:auto-graduate --dry-run
```

### Tidak Ada Output
- Pastikan ada tahun ajaran dengan `aktif = false`
- Pastikan `tanggal_selesai < hari ini`
- Pastikan ada siswa dengan status `Aktif`

### Cek Status Siswa
```sql
-- Lihat siswa yang akan lulus
SELECT s.nama, s.nis, k.nama_kelas, s.tahun_masuk, s.status, ta.tahun_ajaran, ta.tanggal_selesai
FROM siswas s
JOIN kelas k ON s.kelas_id = k.id
JOIN tahun_ajarans ta ON s.tahun_ajaran_id = ta.id
WHERE s.status = 'Aktif'
AND (k.nama_kelas LIKE '%XII%' OR (YEAR(NOW()) - s.tahun_masuk) >= 3);
```

## Status Implementasi ✅

- [x] Auto-Graduate Command
- [x] Dual graduation criteria (Grade XII + 3+ years)
- [x] Dry-run support
- [x] Comprehensive logging
- [x] Scheduled execution
- [x] Safety controls
- [x] Documentation

**Sistem otomatis lulus sudah aktif dan siap digunakan!** 🎉
