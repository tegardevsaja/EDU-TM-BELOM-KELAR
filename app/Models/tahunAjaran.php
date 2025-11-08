<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tahunAjaran extends Model
{
    protected $table = 'tahun_ajarans';

    protected $fillable = [
        'tahun_ajaran',
        'tanggal_mulai',
        'tanggal_selesai',
        'aktif',
    ];
}
