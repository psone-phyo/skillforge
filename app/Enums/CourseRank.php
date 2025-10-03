<?php
namespace App\Enums;

class CourseRank
{
    // Rank IDs
    const ID_BASIC = 'basic';
    const ID_INTERMEDIATE = 'intermediate';
    const ID_ADVANCED = 'advanced';
        const MM = 'Myanmar';
    const EN = 'English';

    // Rank labels
    const BASIC = 'Basic';
    const INTERMEDIATE = 'Intermediate';
    const ADVANCED = 'Advanced';

    // Ranks array
    const AVAILABLES = [
        self::ID_BASIC => self::BASIC,
        self::ID_INTERMEDIATE => self::INTERMEDIATE,
        self::ID_ADVANCED => self::ADVANCED,
    ];

    const COLORS = [
        'success' => self::ID_BASIC,
        'warning' => self::ID_INTERMEDIATE,
        'danger'  => self::ID_ADVANCED,
    ];

    const LANG_COLORS = [
        'success' => self::MM,
        'info' => self::EN,
    ];
}
