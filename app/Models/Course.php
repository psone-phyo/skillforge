<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'instructor_id',
        'title',
        'mm_title',
        'slug',
        'sub_title',
        'mm_sub_title',
        'description',
        'mm_description',
        'level',
        'language',
        'thumbnail_url',
        'is_paid',
        'price',
        'status',
        'published_at',
        'course_code',
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'course_tags', 'course_id', 'tag_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}
