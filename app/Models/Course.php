<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['course_code', 'title', 'semester', 'user_id'];

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function courseUsers()
    {
        return $this->hasMany(CourseUser::class);
    }

    public function leadLecturer()
    {
        return $this->belongsTo(User::class);
    }
}