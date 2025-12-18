<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceSchoolExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected string $periodType; // week|month|year|range
    protected ?string $periodValue; // Y-m (month), Y (year) or null for range/week
    protected ?string $startDate; // for week/range
    protected ?string $endDate;   // for week/range

    public function __construct(string $periodType, ?string $periodValue = null, ?string $startDate = null, ?string $endDate = null)
    {
        $this->periodType = $periodType;
        $this->periodValue = $periodValue;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    protected function sessionsQuery()
    {
        $q = AttendanceSession::query()->orderBy('tanggal');
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

    public function headings(): array
    {
        $sessions = $this->sessionsQuery()->get(['id','tanggal','kelas_id']);

        $headers = ['NIS','Nama','Kelas','Jurusan'];
        foreach ($sessions as $s) {
            $headers[] = 'Tgl ' . Carbon::parse($s->tanggal)->format('d-m-Y');
        }
        return $headers;
    }

    public function collection()
    {
        $sessions = $this->sessionsQuery()->get();
        $sessionIds = $sessions->pluck('id')->all();

        $siswas = Siswa::with(['kelas.jurusan'])
            ->orderBy('kelas_id')
            ->orderBy('nama')
            ->get();

        $records = AttendanceRecord::whereIn('session_id', $sessionIds)
            ->get()
            ->groupBy('session_id')
            ->map(fn($bySession) => $bySession->keyBy('siswa_id'));

        $rows = collect();
        foreach ($siswas as $siswa) {
            $row = [
                $siswa->nis,
                $siswa->nama,
                optional($siswa->kelas)->nama_kelas,
                optional(optional($siswa->kelas)->jurusan)->nama ?? '-',
            ];
            foreach ($sessions as $session) {
                // skip if session is for another class than the student's class
                if ($session->kelas_id !== $siswa->kelas_id) {
                    $row[] = '';
                    continue;
                }
                $rec = optional($records->get($session->id))->get($siswa->id);
                $row[] = $this->mapStatusCode($rec->status ?? null);
            }
            $rows->push($row);
        }

        return $rows;
    }

    protected function mapStatusCode(?string $status): string
    {
        return match ($status) {
            'sakit' => 'S',
            'alfa' => 'A',
            'izin' => 'I',
            'hadir' => 'H',
            default => '-',
        };
    }
}
