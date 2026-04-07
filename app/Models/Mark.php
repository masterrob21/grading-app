<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mark extends Model
{
    protected $fillable = ['enrollment_id', 'assessment_id', 'user_id', 'score', 'is_locked'];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}