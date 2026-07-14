<?php

use Illuminate\Support\Facades\DB;
use App\Models\TimetableSlot;
use App\Models\TeacherLeave;
use App\Models\TeacherAvailability;
use App\Models\TeacherDocument;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Room;
use App\Models\Period;
use App\Models\Subject;
use App\Models\Curriculum;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Schema;

Schema::disableForeignKeyConstraints();

// Delete all school-specific data
TimetableSlot::truncate();
TeacherLeave::truncate();
TeacherAvailability::truncate();
DB::table('teaching_assignments')->truncate();
TeacherDocument::truncate();
Teacher::truncate();
SchoolClass::truncate();
Room::truncate();
Period::truncate();

// Delete subjects and curricula that have a school_id (which means they are not templates)
Subject::whereNotNull('school_id')->delete();
Curriculum::whereNotNull('school_id')->delete();

// Delete all users EXCEPT the Super Admin
User::where('role', '!=', 'super_admin')->delete();

// Finally, delete all schools
School::truncate();

// Also truncate audit logs to start fresh
DB::table('audits')->truncate();

Schema::enableForeignKeyConstraints();

echo "All old school data has been cleared!\n";
