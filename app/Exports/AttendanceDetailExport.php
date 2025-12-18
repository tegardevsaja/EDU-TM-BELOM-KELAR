<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Siswa;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceDetailExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected ?int $kelasId; // null untuk seluruh sekolah
    protected string $periodType; // week|month|year|range
    protected ?string $periodValue; // Y-m (month) | Y (year)
    protected ?string $startDate; // for week/range
    protected ?string $endDate;   // for week/range

    public function __construct(?int $kelasId, string $periodType, ?string $periodValue = null, ?string $startDate = null, ?string $endDate = null)
    {
        $this->kelasId = $kelasId;
        $this->periodType = $periodType;
        $this->periodValue = $periodValue;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function headings(): array
    {
        return ['NIS', 'Nama', 'Kelas', 'Jurusan', 'Tanggal', 'Hari', 'Bulan', 'Kehadiran', 'Keterangan'];
    }

    protected function sessionsQuery()
    {
        $q = AttendanceSession::query()
            ->when($this->kelasId, fn($qq) => $qq->where('kelas_id', $this->kelasId))
            ->orderBy('tanggal');

        if ($this->periodType === 'month' && $this->periodValue) {
            [$y,$m] = explode('-', $this->periodValue);
            $q->whereYear('tanggal', $y)->whereMonth('tanggal', $m);
        } elseif ($this->periodType === 'year' && $this->periodValue) {
            $q->whereYear('tanggal', (int)$this->periodValue);
        } elseif (in_array($this->periodType, ['week','range'])) {
            $q->whereBetween('tanggal', [$this->startDate, $this->endDate]);
        }
        return $q;
    }

    public function collection()
    {
        $sessions = $this->sessionsQuery()->get();
        $sessionIds = $sessions->pluck('id')->all();

        // Siswa scope: per kelas atau semua
        $siswas = Siswa::with(['kelas.jurusan'])
            ->when($this->kelasId, fn($q) => $q->where('kelas_id', $this->kelasId))
            ->orderBy('kelas_id')
            ->orderBy('nama')
            ->get();

        // Preload records
        $records = AttendanceRecord::whereIn('session_id', $sessionIds)
            ->get()
            ->groupBy('session_id')
            ->map(fn($bySession) => $bySession->keyBy('siswa_id'));

        $rows = collect();
        foreach ($sessions as $session) {
            $tanggal = Carbon::parse($session->tanggal);
            $hari = $tanggal->translatedFormat('l');
            $bulan = $tanggal->translatedFormat('F');

            foreach ($siswas as $siswa) {
                // Skip siswa yang bukan dari kelas sesi saat ini jika export seluruh sekolah
                if (!$this->kelasId && $session->kelas_id !== $siswa->kelas_id) {
                    continue;
                }
                $rec = optional($records->get($session->id))->get($siswa->id);
                $status = $rec->status ?? '-';
                $ket = $rec->notes ?? '';

                $rows->push([
                    $siswa->nis,
                    $siswa->nama,
                    optional($siswa->kelas)->nama_kelas,
                    optional(optional($siswa->kelas)->jurusan)->nama ?? '-',
                    $tanggal->format('Y-m-d'),
                    $hari,
                    $bulan,
                    $this->mapStatusFull($status),
                    $ket,
                ]);
            }
        }

        return $rows;
    }

    protected function mapStatusFull(?string $status): string
    {
        return match ($status) {
            'sakit' => 'Sakit',
            'alfa' => 'Alfa',
            'izin' => 'Izin',
            'hadir' => 'Hadir',
            '-', null => '-',
            default => (string)$status,
        };
    }
}
