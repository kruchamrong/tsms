$subjectController = @"
<?php
namespace App\Http\Controllers;
use App\Models\Subject;
use App\Models\SubjectGroup;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectController extends Controller {
    public function index() {
        return Inertia::render('Subject/Index', ['subjects' => Subject::with('subjectGroup')->latest()->paginate(10)]);
    }
    public function create() {
        return Inertia::render('Subject/Create', ['subjectGroups' => SubjectGroup::all()]);
    }
    public function store(Request `$request) {
        Subject::create(`$request->validate([
            'subject_code' => 'required|string|unique:subjects',
            'khmer_name' => 'required|string',
            'english_name' => 'required|string',
            'weekly_hours' => 'required|integer',
            'subject_group_id' => 'nullable|exists:subject_groups,id'
        ]));
        return redirect()->route('subjects.index');
    }
    public function edit(Subject `$subject) {
        return Inertia::render('Subject/Edit', ['subject' => `$subject, 'subjectGroups' => SubjectGroup::all()]);
    }
    public function update(Request `$request, Subject `$subject) {
        `$subject->update(`$request->validate([
            'subject_code' => 'required|string|unique:subjects,subject_code,'.`$subject->id,
            'khmer_name' => 'required|string',
            'english_name' => 'required|string',
            'weekly_hours' => 'required|integer',
            'subject_group_id' => 'nullable|exists:subject_groups,id'
        ]));
        return redirect()->route('subjects.index');
    }
    public function destroy(Subject `$subject) {
        `$subject->delete();
        return redirect()->route('subjects.index');
    }
}
"@
Set-Content "app\Http\Controllers\SubjectController.php" $subjectController

$schoolClassController = @"
<?php
namespace App\Http\Controllers;
use App\Models\SchoolClass;
use App\Models\Grade;
use App\Models\SubjectGroup;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolClassController extends Controller {
    public function index() {
        return Inertia::render('SchoolClass/Index', ['schoolClasses' => SchoolClass::with(['grade', 'subjectGroup'])->latest()->paginate(10)]);
    }
    public function create() {
        return Inertia::render('SchoolClass/Create', ['grades' => Grade::all(), 'subjectGroups' => SubjectGroup::all()]);
    }
    public function store(Request `$request) {
        SchoolClass::create(`$request->validate([
            'class_code' => 'required|string|unique:school_classes',
            'grade_id' => 'required|exists:grades,id',
            'subject_group_id' => 'nullable|exists:subject_groups,id',
            'student_count' => 'required|integer'
        ]));
        return redirect()->route('classes.index');
    }
    public function edit(SchoolClass `$class) {
        return Inertia::render('SchoolClass/Edit', ['schoolClass' => `$class, 'grades' => Grade::all(), 'subjectGroups' => SubjectGroup::all()]);
    }
    public function update(Request `$request, SchoolClass `$class) {
        `$class->update(`$request->validate([
            'class_code' => 'required|string|unique:school_classes,class_code,'.`$class->id,
            'grade_id' => 'required|exists:grades,id',
            'subject_group_id' => 'nullable|exists:subject_groups,id',
            'student_count' => 'required|integer'
        ]));
        return redirect()->route('classes.index');
    }
    public function destroy(SchoolClass `$class) {
        `$class->delete();
        return redirect()->route('classes.index');
    }
}
"@
Set-Content "app\Http\Controllers\SchoolClassController.php" $schoolClassController

$roomController = @"
<?php
namespace App\Http\Controllers;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoomController extends Controller {
    public function index() {
        return Inertia::render('Room/Index', ['rooms' => Room::latest()->paginate(10)]);
    }
    public function create() {
        return Inertia::render('Room/Create');
    }
    public function store(Request `$request) {
        Room::create(`$request->validate([
            'room_name' => 'required|string|unique:rooms',
            'capacity' => 'required|integer',
            'room_type' => 'required|string'
        ]));
        return redirect()->route('rooms.index');
    }
    public function edit(Room `$room) {
        return Inertia::render('Room/Edit', ['room' => `$room]);
    }
    public function update(Request `$request, Room `$room) {
        `$room->update(`$request->validate([
            'room_name' => 'required|string|unique:rooms,room_name,'.`$room->id,
            'capacity' => 'required|integer',
            'room_type' => 'required|string'
        ]));
        return redirect()->route('rooms.index');
    }
    public function destroy(Room `$room) {
        `$room->delete();
        return redirect()->route('rooms.index');
    }
}
"@
Set-Content "app\Http\Controllers\RoomController.php" $roomController
