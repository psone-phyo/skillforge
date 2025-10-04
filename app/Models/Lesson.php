<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class Lesson extends Model
{

    use SoftDeletes, HasRoles;
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
