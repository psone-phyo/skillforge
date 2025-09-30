<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentInterest extends Model
{
    protected $fillable = [
        'student_id',
        'category_id',
    ];
}
