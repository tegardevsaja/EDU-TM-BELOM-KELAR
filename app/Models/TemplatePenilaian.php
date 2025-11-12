<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemplatePenilaian extends Model
{
    use HasFactory;

    protected $table = 'template_penilaian';

    protected $fillable = [
        'nama_template',
        'deskripsi',
        'komponen',
        'created_by',
        'visibility',
    ];

    protected $casts = [
        'komponen' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function penilaian()
    {
        return $this->hasMany(Penilaian::class, 'template_id');
    }

       public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeVisibleFor($query, $user)
    {
        if (method_exists($user, 'hasRole') && ($user->hasRole('master_admin') || $user->hasRole('admin'))) {
            return $query;
        }
        return $query->where('visibility', 'all');
    }
}
