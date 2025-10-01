<?php
namespace App\Enums;

use App\Models\QuizAttempt;

class Table{
    CONST USER = 'users';
    CONST INSTRUCTOR = 'instructors';
    CONST COURSE = 'courses';
    CONST COURSE_CATEGORY = 'course_categories';
    CONST CATEGORY = 'categories';
    const STUDENT_INTEREST = 'student_interests';
    const STUDENT = 'students';
    const PROPOSAL = 'proposals';
    const TAG = 'tags';
    const COURSE_TAG = 'course_tags';
    const ENROLLMENT = 'enrollments';
    const PAYMENT = 'payments';
    const LESSON = 'lessons';
    const REVIEW = 'reviews';
    const CERTIFICATE = 'certificates';

    const QUIZ = 'quizzes';
    const QUIZ_QUESTION = 'quiz_questions';
    const QUIZ_QUESTION_OPTION = 'quiz_question_options';
    const QUIZ_ATTEMPT = 'quiz_attempts';
    const CONVERSATION = 'conversations';
    const MESSAGE = 'messages';
}
