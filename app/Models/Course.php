<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Builder;


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

    public function lessons(){
        return $this->hasMany(Lesson::class, 'course_id');
    }

    public function quiz(){
        return $this->hasOne(Quiz::class, 'course_id');
    }

        public function categories()
    {
        return $this->belongsToMany(Category::class, 'course_categories', 'course_id', 'category_id')
            ->withTimestamps();
    }

        public function reviews()
    {
        return $this->hasMany(Review::class, 'course_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'course_id');
    }

    // Average rating accessor (optional)
    public function scopeWithRatingAvg(Builder $query)
    {
        return $query->withAvg('reviews as rating_avg', 'rating');
    }
}
