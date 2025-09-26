<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use SoftDeletes;
    protected $fillable = [
            'user_id',
            'bio',
            'proposal',
            'cv',
            'profile',
            'status',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
