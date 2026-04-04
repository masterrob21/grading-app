<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = ['year', 'is_current'];

    protected $casts = [
        'is_current' => 'boolean',
    ];
}
