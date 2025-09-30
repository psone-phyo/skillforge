<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'interest_id',
        'bio',
        'profile_url',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
