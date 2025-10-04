<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
        use SoftDeletes;

    protected $fillable = [
        'course_id',
        'instructor_id',
        'title',
        'mm_title',
        'passing_score',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function quizQuestions()
    {
        return $this->hasMany(QuizQuestion::class);
    }
}
