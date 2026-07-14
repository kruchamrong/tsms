<?php
namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Grade;
use App\Models\Curriculum;
use App\Models\Room;
use App\Models\Shift;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class SchoolClassController extends Controller {
    
    public function index(Request $request) {
        $query = SchoolClass::with(['grade', 'curriculum', 'room', 'shift', 'homeroomTeacher'])
            ->join('grades', 'school_classes.grade_id', '=', 'grades.id')
            ->select('school_classes.*');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('school_classes.class_code', 'like', "%{$search}%");
        }

        if ($request->filled('grade_id')) {
            $query->where('school_classes.grade_id', $request->grade_id);
        }

        if ($request->filled('shift_id')) {
            $query->where('school_classes.shift_id', $request->shift_id);
        }

        $classes = $query->orderBy('grades.id', 'desc')->orderBy('school_classes.class_code')->paginate(10)->withQueryString();

        return Inertia::render('SchoolClass/Index', [
            'classes' => $classes,
            'grades' => Grade::orderBy('id', 'desc')->get(),
            'curricula' => Curriculum::orderBy('sort_order')->get(),
            'rooms' => Room::orderBy('room_name')->get(),
            'shifts' => Shift::all(),
            'teachers' => \App\Models\Teacher::orderBy('khmer_name')->get(),
            'filters' => $request->only(['search', 'grade_id', 'shift_id'])
        ]);
    }
    
    public function create() {
        return Inertia::render('SchoolClass/Create', [
            'grades' => Grade::orderBy('id', 'desc')->get(), 
            'curricula' => Curriculum::orderBy('sort_order')->get(),
            'rooms' => Room::orderBy('room_name')->get(),
            'shifts' => Shift::all(),
            'teachers' => \App\Models\Teacher::orderBy('khmer_name')->get(),
        ]);
    }
    
    public function store(Request $request) {
        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where(function($q) {
                    $q->where('school_id', auth()->user()->school_id)
                      ->orWhereNull('school_id');
                });
            }
        };

        $validated = $request->validate([
            'class_code' => ['required', 'string', Rule::unique('school_classes')->where($schoolIdCheck)],
            'grade_id' => ['required', Rule::exists('grades', 'id')->where($schoolIdCheck)],
            'curriculum_id' => ['nullable', Rule::exists('curricula', 'id')->where($schoolIdCheck)],
            'room_id' => ['nullable', Rule::exists('rooms', 'id')->where($schoolIdCheck)],
            'shift_id' => ['nullable', Rule::exists('shifts', 'id')->where($schoolIdCheck)],
            'homeroom_teacher_id' => ['nullable', Rule::exists('teachers', 'id')->where($schoolIdCheck)],
            'student_count' => 'required|integer|min:0'
        ]);

        SchoolClass::create($validated);
        
        return redirect()->route('classes.index')->with('success', 'ថ្នាក់រៀនត្រូវបានបន្ថែមដោយជោគជ័យ។');
    }
    
    public function edit(SchoolClass $class) {
        return Inertia::render('SchoolClass/Edit', [
            'schoolClass' => $class,
            'grades' => Grade::orderBy('id', 'desc')->get(), 
            'curricula' => Curriculum::orderBy('sort_order')->get(),
            'rooms' => Room::orderBy('room_name')->get(),
            'shifts' => Shift::all(),
            'teachers' => \App\Models\Teacher::orderBy('khmer_name')->get(),
        ]);
    }
    
    public function update(Request $request, SchoolClass $class) {
        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where(function($q) {
                    $q->where('school_id', auth()->user()->school_id)
                      ->orWhereNull('school_id');
                });
            }
        };

        $validated = $request->validate([
            'class_code' => ['required', 'string', Rule::unique('school_classes')->ignore($class->id)->where($schoolIdCheck)],
            'grade_id' => ['required', Rule::exists('grades', 'id')->where($schoolIdCheck)],
            'curriculum_id' => ['nullable', Rule::exists('curricula', 'id')->where($schoolIdCheck)],
            'room_id' => ['nullable', Rule::exists('rooms', 'id')->where($schoolIdCheck)],
            'shift_id' => ['nullable', Rule::exists('shifts', 'id')->where($schoolIdCheck)],
            'homeroom_teacher_id' => ['nullable', Rule::exists('teachers', 'id')->where($schoolIdCheck)],
            'student_count' => 'required|integer|min:0'
        ]);

        $class->update($validated);
        
        return back()->with('success', 'ទិន្នន័យត្រូវបានរក្សាទុកដោយជោគជ័យ។');
    }
    
    public function destroy(SchoolClass $class) {
        $class->delete();
        
        return back()->with('success', 'ថ្នាក់រៀនត្រូវបានលុបដោយជោគជ័យ។');
    }


}
