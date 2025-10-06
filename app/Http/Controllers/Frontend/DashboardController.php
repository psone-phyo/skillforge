<?php
namespace App\Http\Controllers\Frontend;

use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Review;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class DashboardController{
    protected $fileUrl;

    public function __construct()
    {
        $this->fileUrl = config('filesystems.disks.r2.url');
    }

    public function index(){
        $categories = Category::all();
        $courses = Course::with(['tags','instructor'])->where('status', CourseStatus::ID_PUBLISHED)->get();
        $fileUrl = $this->fileUrl;
        return Inertia::render('Dashboard', compact('categories', 'courses', 'fileUrl'));
    }

    public function show($id){
        $course = Course::with(['lessons', 'tags'])->find($id);
        $fileUrl = $this->fileUrl;
        $reviewsQuery = Review::with('user')->where('course_id', $id);
        $avgRating = $reviewsQuery->sum('rating') / $reviewsQuery->count();
        $reviews = $reviewsQuery->get();
        $courseStatus = Payment::where('user_id', Auth::id())->where('course_id', $id)->
        where('status', '!=', 'rejected')->first();
        if ($courseStatus){
            $courseStatus = $courseStatus->status;

            if ($courseStatus == 'approved'){
                $course->lessons->each(function($lesson){
                    $lesson['is_locked'] = 0;
                });
            }
        }
        return Inertia::render('CourseDetails', compact('course', 'fileUrl', 'courseStatus', 'reviews', 'avgRating'));
    }

    public function buy($id, Request $request, PaymentService $paymentService){

        $file = $request->file('proof');
        $path = $file->store('payments', 'r2'); // 'r2' = your configured disk
        $total = $request->fee;
        $data = [
            'user_id' => Auth::id(),
            'course_id' => $request->course_id,
            'ref' => $paymentService->generatePaymentRef(),
            'transaction_url' => config('filesystems.disks.r2.url').$path,
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

    public function addReview(Request $request){
        $data = [
            'user_id' => Auth::id(),
            'course_id' => $request->course_id,
            'rating' => $request->rating,
            'comment' => $request->review
        ];
        Review::create($data);
        return back()->with(['success' => 'Review Sent successfully']);
    }


}
