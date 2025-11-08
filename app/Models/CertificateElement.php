<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificateElement extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'field_name',
        'x_position',
        'y_position',
        'font_size',
        'font_family',
        'color',
        'alignment',
    ];

    public function template()
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }
}
    