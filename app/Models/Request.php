<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'course_name', 'course_code', 'request_type', 'seat_number', 'description', 'subject_area', 'screenshot', 'code_url', 'status', 'requested_at', 'ta_id', 'accepted_at', 'completed_at'
        
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function ta()
    {
        return $this->belongsTo(User::class, 'ta_id');
    }

    public function status()
    {
        return $this->hasOne(Status::class);
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class);
    }
}

