<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Course;

class Instructor extends Model
{
    use SoftDeletes, HasRoles;
    protected $fillable = [
        'user_id',
        'proposal_id',
        'title',
        'bio',
        'profile_url',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function courses(){
        return $this->hasMany(Course::class. 'instructor_id');
    }
}
