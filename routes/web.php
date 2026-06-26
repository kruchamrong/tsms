<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TeachingAssignmentController;
use App\Http\Controllers\TeacherAvailabilityController;
use App\Http\Controllers\TimetableSlotController;
use App\Http\Controllers\TeacherLeaveController;
use App\Http\Controllers\SubstituteAssignmentController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::resource('teachers', TeacherController::class);
    Route::resource('subjects', SubjectController::class);
    Route::resource('classes', SchoolClassController::class)->parameters(['classes' => 'class']);
    Route::resource('rooms', RoomController::class);
    Route::resource('teaching-assignments', TeachingAssignmentController::class)->except(['show']);
    Route::resource('teacher-availabilities', TeacherAvailabilityController::class)->only(['index', 'store']);
    
    Route::get('/timetables', [TimetableSlotController::class, 'index'])->name('timetables.index');
    Route::post('/timetables/generate', [TimetableSlotController::class, 'generate'])->name('timetables.generate');

    Route::resource('teacher-leaves', TeacherLeaveController::class)->only(['index', 'store', 'destroy']);
    Route::resource('substitute-assignments', SubstituteAssignmentController::class)->only(['index', 'store']);
});

require __DIR__.'/auth.php';
