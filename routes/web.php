<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TeachingAssignmentController;
use App\Http\Controllers\TeacherAvailabilityController;
use App\Http\Controllers\TeacherDocumentController;
use App\Http\Controllers\TimetableSlotController;
use App\Http\Controllers\TeacherLeaveController;
use App\Http\Controllers\SubstituteAssignmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SchoolProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/setup-database-xyz', function () {
    try {
        $admin = \App\Models\User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
            ]
        );
        $admin->role = 'super_admin';
        $admin->school_id = null;
        $admin->save();

        $seeder = new \Database\Seeders\TemplateDataSeeder();
        $seeder->run();

        return 'Database migrated, Super Admin created, and Template Data seeded successfully! Email: admin@admin.com, Password: password';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/test-periods', function() { return App\Models\Period::all(); });

Route::middleware(['auth', 'is_super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [\App\Http\Controllers\SuperAdminController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\SuperAdminController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\SuperAdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\SuperAdminController::class, 'destroy'])->name('users.destroy');
    Route::get('/health', \Spatie\Health\Http\Controllers\HealthCheckJsonResultsController::class)->name('health');
    Route::get('/health/view', function() { return Inertia::render('SuperAdmin/Health/Index'); })->name('health.view');
    Route::get('/audits', [\App\Http\Controllers\SuperAdmin\AuditController::class, 'index'])->name('audits.index');

    // Global Templates Management
    Route::resource('template-subject-groups', \App\Http\Controllers\SuperAdmin\TemplateSubjectGroupController::class)->except(['create', 'show', 'edit']);
    Route::resource('template-subjects', \App\Http\Controllers\SuperAdmin\TemplateSubjectController::class)->except(['create', 'show', 'edit']);
    Route::post('template-curricula/reorder', [\App\Http\Controllers\SuperAdmin\TemplateCurriculumController::class, 'reorder'])->name('template-curricula.reorder');
    Route::resource('template-curricula', \App\Http\Controllers\SuperAdmin\TemplateCurriculumController::class)
        ->parameters(['template-curricula' => 'template_curriculum'])
        ->except(['create', 'show', 'edit']);
    Route::post('template-curricula/{template_curriculum}/subjects', [\App\Http\Controllers\SuperAdmin\TemplateCurriculumController::class, 'syncSubjects'])->name('template-curricula.sync');
    Route::resource('template-periods', \App\Http\Controllers\SuperAdmin\TemplatePeriodController::class)
        ->parameters(['template-periods' => 'template_period'])
        ->except(['create', 'show', 'edit']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/onboarding', [\App\Http\Controllers\OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/onboarding', [\App\Http\Controllers\OnboardingController::class, 'store'])->name('onboarding.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::middleware('is_school_admin')->group(function () {
        Route::get('/school-profile', [SchoolProfileController::class, 'edit'])->name('school-profile.edit');
        Route::patch('/school-profile', [SchoolProfileController::class, 'update'])->name('school-profile.update');
        
        Route::get('/teachers/template', [TeacherController::class, 'downloadTemplate'])->name('teachers.template');
        Route::post('/teachers/import', [TeacherController::class, 'import'])->name('teachers.import');
        Route::post('/teachers/import-paste', [TeacherController::class, 'importPaste'])->name('teachers.import-paste');
        Route::delete('/teachers/truncate', [TeacherController::class, 'truncate'])->name('teachers.truncate');
        Route::resource('teachers', TeacherController::class);
        Route::post('/subjects/import', [SubjectController::class, 'import'])->name('subjects.import');
        Route::post('/subjects/import-templates', [SubjectController::class, 'importTemplates'])->name('subjects.import-templates');
        Route::get('/subjects/template', [SubjectController::class, 'downloadTemplate'])->name('subjects.template');
        Route::patch('/subjects/{subject}/color', [SubjectController::class, 'updateColor'])->name('subjects.updateColor');
        Route::post('/subjects/reorder', [SubjectController::class, 'reorder'])->name('subjects.reorder');
        Route::resource('subjects', SubjectController::class)->except(['show']);
        
        Route::post('/curricula/reorder', [App\Http\Controllers\CurriculumController::class, 'reorder'])->name('curricula.reorder');
        Route::post('/curricula/import-templates', [App\Http\Controllers\CurriculumController::class, 'importTemplates'])->name('curricula.import-templates');
        Route::resource('curricula', App\Http\Controllers\CurriculumController::class);
        Route::post('/curricula/{curriculum}/subjects/reorder', [App\Http\Controllers\CurriculumController::class, 'reorderSubjects'])->name('curricula.subjects.reorder');
        Route::post('/curricula/{curriculum}/subjects', [App\Http\Controllers\CurriculumController::class, 'attachSubject'])->name('curricula.subjects.attach');
        Route::put('/curricula/{curriculum}/subjects/{subject}', [App\Http\Controllers\CurriculumController::class, 'updateSubject'])->name('curricula.subjects.update');
        Route::delete('/curricula/{curriculum}/subjects/{subject}', [App\Http\Controllers\CurriculumController::class, 'detachSubject'])->name('curricula.subjects.detach');
        
        Route::resource('classes', SchoolClassController::class)->parameters(['classes' => 'class']);
        Route::post('/rooms/{room}/assign-classes', [RoomController::class, 'assignClasses'])->name('rooms.assign-classes');
        Route::resource('rooms', RoomController::class);
        Route::post('/teaching-assignments/reassign', [TeachingAssignmentController::class, 'reassign'])->name('teaching-assignments.reassign');
        Route::delete('/teaching-assignments/truncate', [TeachingAssignmentController::class, 'truncate'])->name('teaching-assignments.truncate');
        Route::delete('/teaching-assignments/group/{teacher}/{subject}', [TeachingAssignmentController::class, 'destroyGroup'])->name('teaching-assignments.group.destroy');
        Route::get('/teaching-assignments/check-class', [TeachingAssignmentController::class, 'checkClassStatus'])->name('teaching-assignments.check-class');
        Route::resource('teaching-assignments', TeachingAssignmentController::class)->except(['show']);
        Route::resource('teacher-availabilities', TeacherAvailabilityController::class)->only(['index', 'store']);
        Route::resource('teacher-documents', TeacherDocumentController::class)->only(['store', 'update', 'destroy']);
        Route::get('/teacher-documents/{id}/download', [TeacherDocumentController::class, 'download'])->name('teacher-documents.download');
        Route::get('/teacher-documents/{id}/show', [TeacherDocumentController::class, 'show'])->name('teacher-documents.show');
        
        Route::get('/timetables', [TimetableSlotController::class, 'index'])->name('timetables.index');
        Route::post('/timetables/slots/toggle', [TimetableSlotController::class, 'toggle'])->name('timetables.slots.toggle');
        Route::post('/timetables/slots/swap', [TimetableSlotController::class, 'swap'])->name('timetables.slots.swap');
        Route::delete('/timetables/slots/truncate', [TimetableSlotController::class, 'truncate'])->name('timetables.slots.truncate');
        Route::delete('/timetables/slots/{id}', [TimetableSlotController::class, 'destroy'])->name('timetables.slots.destroy');

        Route::resource('teacher-leaves', TeacherLeaveController::class)->only(['index', 'store', 'destroy']);
        Route::resource('substitute-assignments', SubstituteAssignmentController::class)->only(['index', 'store']);

        // Reports
        Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/teacher-workloads', [\App\Http\Controllers\ReportController::class, 'teacherWorkloads'])->name('reports.teacher-workloads');
        Route::get('/reports/class-assignments', [\App\Http\Controllers\ReportController::class, 'classAssignments'])->name('reports.class-assignments');
        Route::get('/reports/teacher-timetables', [\App\Http\Controllers\ReportController::class, 'teacherTimetables'])->name('reports.teacher-timetables');
        Route::get('/reports/class-timetables', [\App\Http\Controllers\ReportController::class, 'classTimetables'])->name('reports.class-timetables');
        Route::get('/reports/master-timetables', [\App\Http\Controllers\ReportController::class, 'masterTimetables'])->name('reports.master-timetables');
        Route::get('/reports/homeroom-teachers', [\App\Http\Controllers\ReportController::class, 'homeroomTeachers'])->name('reports.homeroom-teachers');
        Route::get('/reports/test-slots', function() {
            $schoolId = auth()->user()->school_id;
            $classIds = \App\Models\SchoolClass::where('school_id', $schoolId)->pluck('id');
            $slots = \App\Models\TimetableSlot::with(['teachingAssignment.subject', 'teachingAssignment.teacher'])
                ->whereHas('teachingAssignment', function($q) use ($classIds) {
                    $q->whereIn('school_class_id', $classIds);
                })->get();
            return response()->json($slots);
        });
    });
});

require __DIR__.'/auth.php';
