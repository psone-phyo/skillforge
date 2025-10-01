<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class quiz extends Model
{
        use SoftDeletes;

    protected $fillable = [
        'course_id',
        'instructor_id',
        'title',
        'mm_title',
        'passing_score',
    ];
}
