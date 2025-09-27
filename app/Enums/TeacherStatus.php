<?php
namespace App\Enums;

class TeacherStatus{
    CONST ID_PENDING = 'pending';
    CONST ID_APPROVED = 'approved';
    CONST ID_REJECTED = 'rejected';
    CONST ID_SUSPENDED = 'suspended';

    CONST PENDING = 'Pending';
    CONST APPROVED = 'Approved';
    CONST REJECTED = 'Rejected';
    CONST SUSPENDED = 'Suspended';

    CONST AVAILABLES = [
        self::ID_PENDING => self::PENDING,
        self::ID_APPROVED => self::APPROVED,
        self::ID_REJECTED => self::REJECTED,
        self::ID_SUSPENDED => self::SUSPENDED,
    ]

}
