<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'user_one_id',
        'user_two_id',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function participants()
    {
        return User::whereIn('id', [$this->user_one_id, $this->user_two_id])->get();
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
        });
    }

    public function otherPartyFor($userId)
    {
        $otherId = $this->user_one_id == $userId ? $this->user_two_id : $this->user_one_id;
        return User::find($otherId);
    }
}
