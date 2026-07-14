<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\SubjectGroup;
use Inertia\Inertia;

class TemplateSubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::with('subjectGroup')->whereNull('school_id');

        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('khmer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('english_name', 'like', '%' . $request->search . '%')
                  ->orWhere('short_name', 'like', '%' . $request->search . '%');
            });
        }

        $subjects = $query->orderBy('subject_code')->paginate(50)->withQueryString();
        $groups = SubjectGroup::whereNull('school_id')->get();

        return Inertia::render('SuperAdmin/Templates/Subjects/Index', [
            'subjects' => $subjects,
            'groups' => $groups,
            'filters' => $request->only('search')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_code' => 'required|string|max:50',
            'khmer_name' => 'required|string|max:255',
            'english_name' => 'nullable|string|max:255',
            'short_name' => 'required|string|max:50',
            'color' => 'nullable|string|max:20',
            'subject_group_id' => 'nullable|exists:subject_groups,id'
        ]);

        Subject::create([
            'subject_code' => $request->subject_code,
            'khmer_name' => $request->khmer_name,
            'english_name' => $request->english_name,
            'short_name' => $request->short_name,
            'color' => $request->color,
            'subject_group_id' => $request->subject_group_id,
            'school_id' => null
        ]);

        return redirect()->back()->with('message', 'បង្កើតមុខវិជ្ជាគំរូបានជោគជ័យ។');
    }

    public function update(Request $request, Subject $template_subject)
    {
        if ($template_subject->school_id !== null) {
            abort(403);
        }

        $request->validate([
            'subject_code' => 'required|string|max:50',
            'khmer_name' => 'required|string|max:255',
            'english_name' => 'nullable|string|max:255',
            'short_name' => 'required|string|max:50',
            'color' => 'nullable|string|max:20',
            'subject_group_id' => 'nullable|exists:subject_groups,id'
        ]);

        $template_subject->update([
            'subject_code' => $request->subject_code,
            'khmer_name' => $request->khmer_name,
            'english_name' => $request->english_name,
            'short_name' => $request->short_name,
            'color' => $request->color,
            'subject_group_id' => $request->subject_group_id
        ]);

        return redirect()->back()->with('message', 'កែប្រែមុខវិជ្ជាគំរូបានជោគជ័យ។');
    }

    public function destroy(Subject $template_subject)
    {
        if ($template_subject->school_id !== null) {
            abort(403);
        }

        $template_subject->delete();

        return redirect()->back()->with('message', 'លុបមុខវិជ្ជាគំរូបានជោគជ័យ។');
    }
}
