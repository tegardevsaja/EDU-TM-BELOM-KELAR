<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'left_label',
        'left_name',
        'left_org',
        'right_label',
        'right_name',
        'right_org',
        'city',
        'left_signature_path',
        'right_signature_path',
    ];
}
