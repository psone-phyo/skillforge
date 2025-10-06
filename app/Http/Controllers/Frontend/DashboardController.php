<?php
namespace App\Http\Controllers\Frontend;

use App\Enums\CourseStatus;
use App\Models\Category;
use App\Models\Course;
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
        return Inertia::render('CourseDetails', compact('course', 'fileUrl'));
    }
}
