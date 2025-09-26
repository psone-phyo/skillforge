<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
