<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = ['course_id', 'title', 'max_score', 'weight'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }
}
