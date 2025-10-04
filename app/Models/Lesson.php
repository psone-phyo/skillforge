<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{

    use SoftDeletes;
    protected $fillable = [
        'course_id',
        'title',
        'mm_title',
        'description',
        'mm_description',
        'video_url',
        'is_locked',
        'sort',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
