<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory;
    
    protected $table = 'jurusans'; // 👈 Cukup satu kali saja!

    protected $fillable = [
        'nama_jurusan',
    ];

    // Relasi (opsional, jika diperlukan)
    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'jurusan_id');
    }
}