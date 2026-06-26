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
    public function store(Request $request) {
        Subject::create($request->validate([
            'subject_code' => 'required|string|unique:subjects',
            'khmer_name' => 'required|string',
            'english_name' => 'required|string',
            'weekly_hours' => 'required|integer',
            'subject_group_id' => 'nullable|exists:subject_groups,id'
        ]));
        return redirect()->route('subjects.index');
    }
    public function edit(Subject $subject) {
        return Inertia::render('Subject/Edit', ['subject' => $subject, 'subjectGroups' => SubjectGroup::all()]);
    }
    public function update(Request $request, Subject $subject) {
        $subject->update($request->validate([
            'subject_code' => 'required|string|unique:subjects,subject_code,'.$subject->id,
            'khmer_name' => 'required|string',
            'english_name' => 'required|string',
            'weekly_hours' => 'required|integer',
            'subject_group_id' => 'nullable|exists:subject_groups,id'
        ]));
        return redirect()->route('subjects.index');
    }
    public function destroy(Subject $subject) {
        $subject->delete();
        return redirect()->route('subjects.index');
    }
}
