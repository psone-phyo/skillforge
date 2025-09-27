<?php
namespace App\Enums;

class CourseRank
{
    // Rank IDs
    const ID_BASIC = 'basic';
    const ID_INTERMEDIATE = 'intermediate';
    const ID_ADVANCED = 'advanced';

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
}
