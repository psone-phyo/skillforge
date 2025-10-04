<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestionOption extends Model
{
    protected $fillable = [
        'quiz_question_id',
        'answer',
        'is_correct',
        'sort',
    ];

    public function quizQuestion()
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }
}
