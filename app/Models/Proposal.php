<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $fillable = [
        'instructor_id',
        'proposal',
        'cv',
        'category_id',
    ];

    public function user(){
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }
}
