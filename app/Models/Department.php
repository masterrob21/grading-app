<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['department_name'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
