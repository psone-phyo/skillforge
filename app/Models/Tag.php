<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable =[
        'name',
        'slug',
    ];

        public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_tags', 'tag_id', 'course_id');
    }
}
