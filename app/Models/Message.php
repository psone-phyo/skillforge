<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
        use SoftDeletes;

    protected $fillable = [
        'message',
        'conversation_id',
        'sender_id',
        'is_read',
    ];
}
