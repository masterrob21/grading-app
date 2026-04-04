<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'student_id',
        'full_name',
        'department_id',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
