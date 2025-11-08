<?php

namespace App\Enums;

enum UserRole: string
{
    case Master = 'master_admin';
    case Admin = 'admin';
    case Guru = 'guru';
}
