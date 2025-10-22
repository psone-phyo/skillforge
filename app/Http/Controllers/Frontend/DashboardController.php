<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Payment;
use App\Models\QuizAttempt;
use App\Models\Review;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class DashboardController
{
    protected $fileUrl;

    public function __construct()
    {
        $this->fileUrl = config('filesystems.disks.r2.url');
    }

    public function index()
    {
        $categories = Category::all();
        $courses = Course::with(['tags', 'instructor'])->where('status', CourseStatus::ID_PUBLISHED)->get();
        $fileUrl = $this->fileUrl;
        $instructor_status = Auth::user()->instructor->status ?? 'none';
        return Inertia::render('Dashboard', compact('categories', 'courses', 'fileUrl', 'instructor_status'));
    }

    public function show($id)
    {
        $course = Course::with(['lessons', 'tags', 'instructor.courses', 'quiz.quizQuestions'])->find($id);
        $fileUrl = $this->fileUrl;
        $reviewsQuery = Review::with('user')->where('course_id', $id);
        $avgRating = $reviewsQuery->count() == 0 ? 0 : $reviewsQuery->sum('rating') / $reviewsQuery->count();
        $reviews = $reviewsQuery->get();
        if ($course->is_paid == false) {
            $courseStatus = 'free';
        } else {
            $courseStatus = Payment::where('user_id', Auth::id())->where('course_id', $id)->where('status', '!=', 'rejected')->first();
        }

        if ($courseStatus) {
            $courseStatus = $courseStatus == 'free' ? $courseStatus : $courseStatus->status;

            if ($courseStatus == 'approved' || $courseStatus == 'free') {
                $course->lessons->each(function ($lesson) {
                    $lesson['is_locked'] = 0;
                });
            }
        }
        $quizTotalScore = 0;
        if ($course->quiz){
            $quizTotalScore = $course->quiz->quizQuestions->sum('point');
        }
        $quizScore = QuizAttempt::where('quiz_id', optional($course->quiz)->id)->where('user_id', Auth::id())->orderBy('updated_at', 'desc')->first();
        return Inertia::render('CourseDetails', compact('course', 'fileUrl', 'courseStatus', 'reviews', 'avgRating', 'quizScore', 'quizTotalScore'));
    }

    public function buy($id, Request $request, PaymentService $paymentService)
    {

        $file = $request->file('proof');
        $path = $file->store('payments', 'r2'); // 'r2' = your configured disk
        $total = $request->fee;
        $data = [
            'user_id' => Auth::id(),
            'course_id' => $request->course_id,
            'ref' => $paymentService->generatePaymentRef(),
            'transaction_url' => config('filesystems.disks.r2.url') . $path,
            'course_fee' => $request->fee,
            'total_amount' => $total,
            'payment_method' => 'MMQR',
            'status' => 'pending',
            'comission' => 0,
            'note' => $request->note ?? null,
        ];

        Payment::create($data);
        return back()->with(['success' => 'Payment uploaded successfully']);
    }

    public function addReview(Request $request)
    {
        $data = [
            'user_id' => Auth::id(),
            'course_id' => $request->course_id,
            'rating' => $request->rating,
            'comment' => $request->review
        ];
        Review::create($data);
        // return back()->with(['success' => 'Review Sent successfully']);
    }

    public function getCertificate($course_id){
        if (!$course_id) return back();

        $certificate = Certificate::with(['user','course.instructor'])->where('user_id', Auth::id())->where('course_id', $course_id)->first();
        if ($certificate){
            return view('certificates.show', compact('certificate'));
        }else{
            return back();
        }
    }

    public function myLibrary(Request $request)
    {
        $userId = Auth::id();

        // Purchases (approved)
        $purchases = Payment::query()
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->with(['course' => function ($q) {
                $q->select('id', 'title', 'sub_title', 'level', 'language', 'thumbnail_url', 'slug', 'course_code', 'is_paid', 'price');
            }])
            ->orderByDesc('purchased_at')
            ->get()
            ->map(function ($p) {
                return [
                    'id'           => $p->id,
                    'course_id'    => $p->course_id,
                    'ref'          => $p->ref,
                    'course_fee'   => $p->course_fee,
                    'total_amount' => $p->total_amount,
                    'payment_method'=> $p->payment_method,
                    'purchased_at' => optional($p->purchased_at)->toIso8601String(),
                    'course'       => [
                        'id'            => $p->course->id ?? null,
                        'title'         => $p->course->title ?? null,
                        'mm_title'         => $p->course->mm_title ?? null,
                        'sub_title'     => $p->course->sub_title ?? null,
                        'mm_sub_title'     => $p->course->mm_sub_title ?? null,
                        'slug'          => $p->course->slug ?? null,
                        'course_code'   => $p->course->course_code ?? null,
                        'level'         => $p->course->level ?? null,
                        'language'      => $p->course->language ?? null,
                        'thumbnail_url' => $p->course->thumbnail_url ?? null,
                        'is_paid'       => $p->course->is_paid ?? false,
                        'price'         => $p->course->price ?? null,
                    ],
                ];
            });

        // Certificates
        $certificates = Certificate::query()
            ->where('user_id', $userId)
            ->with(['course' => function ($q) {
                $q->select('id', 'title', 'slug', 'thumbnail_url', 'course_code', 'level', 'language');
            }])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($c) {
                return [
                    'id'             => $c->id,
                    'course_id'      => $c->course_id,
                    'issue_number'   => $c->issue_number,
                    'issue_date'     => $c->issue_date,
                    'certificate_url'=> $c->certificate_url,
                    'course'         => [
                        'id'            => $c->course->id ?? null,
                        'title'         => $c->course->title ?? null,
                        'mm_title'         => $c->course->mm_title ?? null,
                        'slug'          => $c->course->slug ?? null,
                        'course_code'   => $c->course->course_code ?? null,
                        'level'         => $c->course->level ?? null,
                        'language'      => $c->course->language ?? null,
                        'thumbnail_url' => $c->course->thumbnail_url ?? null,
                    ],
                ];
            });

        return Inertia::render('MyLibrary', [
            'purchases'   => $purchases,
            'certificates'=> $certificates,
        ]);
    }
}
