<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';

    protected $fillable = [
        'siswa_id',
        'guru_id',
        'jenis_penilaian',
        'template_id',
        'nilai',
        'nilai_detail',
        'keterangan',
        'tanggal_input',
        'tahun_ajaran_id',
        'visibility',
    ];  

    protected $dates = ['tanggal_input'];

     protected $casts = [
        'nilai_detail' => 'array',
     ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function template()
    {
        return $this->belongsTo(TemplatePenilaian::class, 'template_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function scopeVisibleFor($query, $user)
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return $query;
        }
        return $query->where('visibility', 'all');
    }
}
