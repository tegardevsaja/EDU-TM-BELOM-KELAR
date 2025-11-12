<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id', 'siswa_id', 'status', 'notes'
    ];

    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
