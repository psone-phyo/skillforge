<?php
namespace App\Enums;

class Role{
    CONST ID_ADMIN = 'admin';
    CONST ID_STAFF = 'staff';
    CONST ID_TEACHER = 'teacher';
    CONST ID_STUDENT = 'student';

    CONST ADMIN = 'Admin';
    CONST STAFF = 'Staff';
    CONST TEACHER = 'Teacher';
    CONST STUDENT = 'Student';

    CONST AVAILABLES = [
        self::ID_ADMIN => self::ADMIN,
        self::ID_STAFF => self::STAFF,
        self::ID_TEACHER => self::TEACHER,
        self::ID_STUDENT => self::STUDENT,
    ];

    CONST CREATE_USER_ROLES = [
        self::ID_ADMIN => self::ADMIN,
        self::ID_STAFF => self::STAFF,
        self::ID_STUDENT => self::STUDENT,
    ];
}
