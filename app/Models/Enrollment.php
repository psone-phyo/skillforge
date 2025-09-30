<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'ref',
        'transaction_url',
        'transaction_number',
        'course_fee',
        'comission',
        'total_amount',
        'payment_method',
        'purchased_at',
    ];
}
