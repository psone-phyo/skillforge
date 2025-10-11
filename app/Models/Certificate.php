<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Course;
class Certificate extends Model
{
        use SoftDeletes;

    protected $fillable = [
        'course_id',
        'user_id',
        'issue_number',
        'issue_date',
        'certificate_url',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

        public function course(){
        return $this->belongsTo(Course::class, 'course_id');
    }
}
