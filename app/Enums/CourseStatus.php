<?php

namespace App\Enums;

class CourseStatus
{
    const ID_ON_PROGRESS = 'on_progress';
    const ID_FINISHED = 'finished';
    const ID_PUBLISHED = 'published';
    const ID_HIDE = 'inactive';

    const ON_PROGRESS = 'On Progress';
    const FINISHED = 'Finished';
    const PUBLISHED = 'Published';
    const HIDE = 'Inactive';

    const AVAILABLES = [
        self::ID_ON_PROGRESS => self::ON_PROGRESS,
        self::ID_FINISHED => self::FINISHED,
        self::ID_PUBLISHED => self::PUBLISHED,
        self::ID_HIDE => self::HIDE,
    ];

    const COLORS = [
        'warning' => self::ID_ON_PROGRESS,
        'success' => self::ID_FINISHED,
        'info' => self::ID_PUBLISHED,
        'danger'    => self::ID_HIDE,
    ];
}
