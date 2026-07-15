<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Curriculum;
use App\Models\Subject;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TemplateCurriculumController extends Controller
{
    public function index(Request $request)
    {
        $query = Curriculum::with('subjects')->whereNull('school_id');

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $curricula = $query->orderBy('sort_order')->paginate(50)->withQueryString();
        $subjects = Subject::whereNull('school_id')->orderBy('subject_code')->get();

        return Inertia::render('SuperAdmin/Templates/Curricula/Index', [
            'curricula' => $curricula,
            'subjects' => $subjects,
            'filters' => $request->only('search')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0'
        ]);

        Curriculum::create([
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'school_id' => null
        ]);

        return redirect()->back()->with('message', 'បង្កើតកម្មវិធីសិក្សាគំរូបានជោគជ័យ។');
    }

    public function update(Request $request, Curriculum $template_curriculum)
    {
        if ($template_curriculum->school_id !== null) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0'
        ]);

        $template_curriculum->update([
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0
        ]);

        return redirect()->back()->with('message', 'កែប្រែកម្មវិធីសិក្សាគំរូបានជោគជ័យ។');
    }

    public function destroy(Curriculum $template_curriculum)
    {
        if ($template_curriculum->school_id !== null) {
            abort(403);
        }

        $template_curriculum->delete();

        return redirect()->back()->with('message', 'លុបកម្មវិធីសិក្សាគំរូបានជោគជ័យ។');
    }

    public function syncSubjects(Request $request, Curriculum $template_curriculum)
    {
        if ($template_curriculum->school_id !== null) {
            abort(403);
        }

        $request->validate([
            'subjects' => 'array',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.weekly_hours' => 'required|integer|min:1'
        ]);

        $syncData = [];
        $sortOrder = 1;
        foreach ($request->subjects as $subjectData) {
            $syncData[$subjectData['subject_id']] = [
                'weekly_hours' => $subjectData['weekly_hours'],
                'sort_order' => $sortOrder++
            ];
        }

        $template_curriculum->subjects()->sync($syncData);

        return redirect()->back()->with('message', 'ចងមុខវិជ្ជាទៅកម្មវិធីសិក្សាគំរូបានជោគជ័យ។');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'curricula' => 'required|array',
            'curricula.*.id' => 'required|exists:curricula,id',
            'curricula.*.sort_order' => 'required|integer'
        ]);

        foreach ($request->curricula as $curriculumData) {
            Curriculum::where('id', $curriculumData['id'])
                ->whereNull('school_id')
                ->update(['sort_order' => $curriculumData['sort_order']]);
        }

        return redirect()->back()->with('message', 'បានរៀបចំលំដាប់ថ្នាក់កម្មវិធីសិក្សាគំរូដោយជោគជ័យ។');
    }
}
