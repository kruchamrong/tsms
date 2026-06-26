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
    public function store(Request $request) {
        SchoolClass::create($request->validate([
            'class_code' => 'required|string|unique:school_classes',
            'grade_id' => 'required|exists:grades,id',
            'subject_group_id' => 'nullable|exists:subject_groups,id',
            'student_count' => 'required|integer'
        ]));
        return redirect()->route('classes.index');
    }
    public function edit(SchoolClass $class) {
        return Inertia::render('SchoolClass/Edit', ['schoolClass' => $class, 'grades' => Grade::all(), 'subjectGroups' => SubjectGroup::all()]);
    }
    public function update(Request $request, SchoolClass $class) {
        $class->update($request->validate([
            'class_code' => 'required|string|unique:school_classes,class_code,'.$class->id,
            'grade_id' => 'required|exists:grades,id',
            'subject_group_id' => 'nullable|exists:subject_groups,id',
            'student_count' => 'required|integer'
        ]));
        return redirect()->route('classes.index');
    }
    public function destroy(SchoolClass $class) {
        $class->delete();
        return redirect()->route('classes.index');
    }
}
