<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengguna extends Model
{
    use HasFactory;
    protected $table = 'penggunas';

     protected $fillable = [
        'nama',
        'email',
        'nik',
    ];
    
    public function user()
    {
        return $this->hasOne(User::class, 'pengguna_id');
    }

}
