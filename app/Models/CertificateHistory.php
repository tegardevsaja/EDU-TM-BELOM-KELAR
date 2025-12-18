<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateHistory extends Model
{
    protected $table = 'certificate_histories';

    protected $fillable = [
        'template_id',
        'kelas_id',
        'jumlah_siswa',
        'jenis_file',
        'generated_by',
    ];
}
