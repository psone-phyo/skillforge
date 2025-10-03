<?php
namespace App\Enums;

class InstructorStatus{
    const ID_PENDING = 'pending';
    const ID_APPROVED = 'approved';
    const ID_REJECTED = 'rejected';
    const ID_SUSPENDED = 'suspended';
        const PENDING = 'Pending';
    const APPROVED = 'Approved';
    const REJECTED = 'Rejected';
    const SUSPENDED = 'Suspended';

    const STATUSES = [
        self::ID_PENDING   => self::PENDING,
        self::ID_APPROVED  => self::APPROVED,
        self::ID_REJECTED  => self::REJECTED,
        self::ID_SUSPENDED => self::SUSPENDED,
    ];

    const COLORS = [
        'warning' => self::ID_PENDING,
        'success' => self::ID_APPROVED,
        'danger'  => self::ID_REJECTED,
        'gray'    => self::ID_SUSPENDED,
    ];

}
