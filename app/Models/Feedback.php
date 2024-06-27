<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
    protected $fillable = [
        'request_id', 'student_id', 'rating', 'comments'
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
