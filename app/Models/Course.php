<?php

namespace App\Models;

use App\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'mm_title',
        'description',
        'mm_description',
        'outline',
        'mm_outline',
        'is_free',
        'price',
        'status',
    ];

    public function getCourseTypeAttribute(): string
    {
        return $this->is_free ? 'Free' : 'Paid';
    }
}
