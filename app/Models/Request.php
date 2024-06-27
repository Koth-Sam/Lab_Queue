<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id', 'course_name', 'course_code', 'request_type','description', 'subject_area', 'seat_number', 'screenshot', 'code_url', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'student_id');
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
