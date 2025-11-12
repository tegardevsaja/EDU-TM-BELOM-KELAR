<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id', 'tahun_ajaran_id', 'granted', 'reason', 'granted_by'
    ];

    protected $casts = [
        'granted' => 'boolean',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function granter()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
