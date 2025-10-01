<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
        use SoftDeletes;

    protected $fillable = [
        'course_id',
        'user_id',
        'issue_number',
        'issue_date',
        'certificate_url',
    ];
}
