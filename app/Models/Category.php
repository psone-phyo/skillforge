<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description'
    ];

        public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_categories', 'category_id', 'course_id')
            ->withTimestamps();
    }
}
