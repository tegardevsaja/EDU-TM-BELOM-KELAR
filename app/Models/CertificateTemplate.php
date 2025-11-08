<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $table = 'certificate_templates';

    protected $fillable = [
        'nama_template',
        'background_image',
    ];

    /**
     * Relasi: satu template punya banyak elemen sertifikat
     */
    public function elements()
    {
        return $this->hasMany(CertificateElement::class, 'template_id');
    }
}
