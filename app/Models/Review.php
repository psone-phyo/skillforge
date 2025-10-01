<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
        use SoftDeletes;

    protected $fillable = [
        'user_id',
        'course_id',
        'rating',
        'comment',
    ];
}
