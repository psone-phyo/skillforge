<?php
namespace App\Http\Controllers\Frontend;

use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
        return Inertia::render('CourseDetails', compact('course', 'fileUrl', 'courseStatus'));
    }

    public function buy($id, Request $request){
            $file = $request->file('proof');
            $path = $file->store('payments', 'r2'); // 'r2' = your configured disk
        $total = $request->fee;
        $data = [
            'user_id' => Auth::id(),
            'course_id' => $request->course_id,
            'ref' => $this->generatePaymentRef(),
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

    function generatePaymentRef(): string
{
    $lastRef = Payment::latest('id')->value('ref') ?? 'AAAAAA';

    $chars = str_split($lastRef);
    $i = count($chars) - 1;

    while ($i >= 0) {
        if ($chars[$i] === 'Z') {
            $chars[$i] = 'A';
            $i--;
        } else {
            $chars[$i] = chr(ord($chars[$i]) + 1);
            break;
        }
    }

    if ($i < 0) {
        array_unshift($chars, 'A');
    }

    return implode('', $chars);
}
}
