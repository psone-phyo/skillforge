<?php

use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\Frontend\QuizController;
use App\Http\Controllers\Frontend\ReviewController;
use App\Models\Course;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

Route::get('/', function () {
    if(!Auth::check()){
        return Inertia::render('Welcome');
    }
    return to_route('dashboard');
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/teacher/cv/{filename}', function ($filename) {
    $path = "private/{$filename}";

    if (!Storage::exists($path)) {
        abort(404);
    }

    return Storage::download($path);
})
    ->name('teacher.cv.download');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';

// web.php
Route::delete('/courses/{course}/tags/{tag}', function(Course $course, $tag){
    $course->tags()->detach($tag);
    return back()->with('success', 'Tag removed!');
})->name('courses.detach-tag');

Route::get('/course/{id}', [DashboardController::class, 'show'])->name('course-details');

//Payment
Route::post('course/{id}/payments', [DashboardController::class, 'buy'])->name('course.buy');
Route::post('/stripe/create-payment', [PaymentController::class, 'createPaymentIntent']);
Route::get('/payment-success', [PaymentController::class, 'success']);
Route::get('/payment-cancel', [PaymentController::class, 'cancel']);
Route::post('/add-review', [ReviewController::class, 'addReview']);
Route::get('/delete-review/{id}', [ReviewController::class, 'delete']);
Route::get('/course/{id}/quiz', [QuizController::class, 'get']);
Route::post('/take-quiz', [QuizController::class, 'submit']);


