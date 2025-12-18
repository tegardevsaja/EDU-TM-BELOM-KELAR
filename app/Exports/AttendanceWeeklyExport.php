<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Siswa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceWeeklyExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected int $kelasId;
    protected string $startDate; // Y-m-d
    protected string $endDate;   // Y-m-d

    public function __construct(int $kelasId, string $startDate, string $endDate)
    {
        $this->kelasId = $kelasId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function headings(): array
    {
        $sessions = AttendanceSession::where('kelas_id', $this->kelasId)
            ->whereBetween('tanggal', [$this->startDate, $this->endDate])
            ->orderBy('tanggal')
            ->get(['id', 'tanggal']);

        $headers = ['NIS', 'Nama', 'Kelas', 'Jurusan'];
        foreach ($sessions as $s) {
            $headers[] = 'Tgl ' . Carbon::parse($s->tanggal)->format('d-m-Y');
        }
        return $headers;
    }

    public function collection()
    {
        $sessions = AttendanceSession::where('kelas_id', $this->kelasId)
            ->whereBetween('tanggal', [$this->startDate, $this->endDate])
            ->orderBy('tanggal')
            ->get();

        $sessionIds = $sessions->pluck('id')->all();

        $siswas = Siswa::with(['kelas.jurusan'])
            ->where('kelas_id', $this->kelasId)
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
                optional($siswa->jurusan)->nama ?? '-',
            ];
            foreach ($sessions as $session) {
                $rec = optional($records->get($session->id))->get($siswa->id);
                $status = $rec->status ?? null;
                $row[] = $this->mapStatusCode($status);
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
