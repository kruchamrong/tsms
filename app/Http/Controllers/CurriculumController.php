<?php

namespace App\Http\Controllers;

use App\Models\Curriculum;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CurriculumController extends Controller
{
    public function index()
    {
        $query = Curriculum::withCount('subjects')->withSum('subjects as total_hours', 'curriculum_subject.weekly_hours')->orderBy('sort_order');
        
        if (is_null(auth()->user()->school_id)) {
            $query->whereNull('school_id');
        }
        
        $curricula = $query->get();
        return Inertia::render('Curriculum/Index', [
            'curricula' => $curricula
        ]);
    }

    public function create()
    {
        return Inertia::render('Curriculum/Create');
    }

    public function store(Request $request)
    {
        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where('school_id', auth()->user()->school_id);
            }
        };

        $validated = $request->validate([
            'name' => ['required', 'string', Rule::unique('curricula')->where($schoolIdCheck)],
            'description' => 'nullable|string'
        ]);

        $validated['sort_order'] = Curriculum::max('sort_order') + 1;

        Curriculum::create($validated);

        return redirect()->route('curricula.index')->with('success', 'កម្មវិធីសិក្សាត្រូវបានបន្ថែមដោយជោគជ័យ។');
    }

    public function show(Curriculum $curriculum)
    {
        // Get subjects already attached
        $attachedSubjectIds = $curriculum->subjects()->pluck('subjects.id')->toArray();

        // Get subjects available to attach
        $availableSubjects = Subject::whereNotIn('id', $attachedSubjectIds)
                                    ->orderBy('subject_code')
                                    ->get();

        $curriculum->load(['subjects' => function ($query) {
            $query->orderBy('subject_code');
        }]);

        return Inertia::render('Curriculum/Show', [
            'curriculum' => $curriculum,
            'availableSubjects' => $availableSubjects
        ]);
    }

    public function edit(Curriculum $curriculum)
    {
        return Inertia::render('Curriculum/Edit', [
            'curriculum' => $curriculum
        ]);
    }

    public function update(Request $request, Curriculum $curriculum)
    {
        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where('school_id', auth()->user()->school_id);
            }
        };

        $validated = $request->validate([
            'name' => ['required', 'string', Rule::unique('curricula')->ignore($curriculum->id)->where($schoolIdCheck)],
            'description' => 'nullable|string'
        ]);

        $curriculum->update($validated);

        return redirect()->route('curricula.index')->with('success', 'កម្មវិធីសិក្សាត្រូវបានកែប្រែដោយជោគជ័យ។');
    }

    public function destroy(Curriculum $curriculum)
    {
        $curriculum->delete();
        return redirect()->route('curricula.index')->with('success', 'កម្មវិធីសិក្សាត្រូវបានលុបដោយជោគជ័យ។');
    }

    public function attachSubject(Request $request, Curriculum $curriculum)
    {
        if ($curriculum->school_id === null && auth()->user()->role !== 'super_admin') {
            abort(403, 'មិនអាចកែប្រែកម្មវិធីសិក្សាគំរូបានទេ!');
        }

        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where('school_id', auth()->user()->school_id);
            }
        };

        $request->validate([
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where($schoolIdCheck)],
            'weekly_hours' => 'required|integer|min:0|max:40'
        ]);

        $maxSortOrder = \DB::table('curriculum_subject')->where('curriculum_id', $curriculum->id)->max('sort_order') ?? -1;

        $curriculum->subjects()->syncWithoutDetaching([
            $request->subject_id => [
                'weekly_hours' => $request->weekly_hours,
                'sort_order' => $maxSortOrder + 1
            ]
        ]);

        return redirect()->back()->with('success', 'មុខវិជ្ជាត្រូវបានបន្ថែមចូលកម្មវិធីសិក្សាដោយជោគជ័យ។');
    }

    public function updateSubject(Request $request, Curriculum $curriculum, Subject $subject)
    {
        if ($curriculum->school_id === null && auth()->user()->role !== 'super_admin') {
            abort(403, 'មិនអាចកែប្រែកម្មវិធីសិក្សាគំរូបានទេ!');
        }

        $request->validate([
            'weekly_hours' => 'required|integer|min:0|max:40'
        ]);

        $curriculum->subjects()->updateExistingPivot($subject->id, [
            'weekly_hours' => $request->weekly_hours
        ]);

        return redirect()->back()->with('success', 'ទិន្នន័យត្រូវបានរក្សាទុកដោយជោគជ័យ។');
    }

    public function detachSubject(Curriculum $curriculum, Subject $subject)
    {
        if ($curriculum->school_id === null && auth()->user()->role !== 'super_admin') {
            abort(403, 'មិនអាចកែប្រែកម្មវិធីសិក្សាគំរូបានទេ!');
        }

        $curriculum->subjects()->detach($subject->id);
        
        return back()->with('success', 'មុខវិជ្ជាត្រូវបានដកចេញពីកម្មវិធីសិក្សាដោយជោគជ័យ។');
    }

    public function reorderSubjects(Request $request, Curriculum $curriculum)
    {
        if ($curriculum->school_id === null && auth()->user()->role !== 'super_admin') {
            abort(403, 'មិនអាចកែប្រែកម្មវិធីសិក្សាគំរូបានទេ!');
        }
        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where('school_id', auth()->user()->school_id);
            }
        };

        $validated = $request->validate([
            'subject_ids' => 'required|array',
            'subject_ids.*' => [Rule::exists('subjects', 'id')->where($schoolIdCheck)]
        ]);

        DB::transaction(function () use ($validated, $curriculum) {
            foreach ($validated['subject_ids'] as $index => $id) {
                $curriculum->subjects()->updateExistingPivot($id, ['sort_order' => $index]);
            }
        });

        return back()->with('success', 'ទិន្នន័យត្រូវបានរក្សាទុកដោយជោគជ័យ។');
    }

    public function reorder(Request $request)
    {
        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where('school_id', auth()->user()->school_id);
            }
        };

        $validated = $request->validate([
            'curriculum_ids' => 'required|array',
            'curriculum_ids.*' => [Rule::exists('curricula', 'id')->where($schoolIdCheck)]
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['curriculum_ids'] as $index => $id) {
                Curriculum::where('id', $id)->update(['sort_order' => $index]);
            }
        });

        return back()->with('success', 'លំដាប់ត្រូវបានកែប្រែដោយជោគជ័យ។');
    }

    public function importTemplates(Request $request)
    {
        if (auth()->user()->role === 'super_admin') {
            return redirect()->back()->with('error', 'Super Admin មិនអាចនាំចូលកម្មវិធីសិក្សាគំរូបានទេ។');
        }

        $globalCurricula = Curriculum::withoutGlobalScope(\App\Models\Scopes\SchoolScope::class)
            ->with(['subjects' => function ($query) {
                $query->withoutGlobalScope(\App\Models\Scopes\SchoolScope::class);
            }])
            ->whereNull('school_id')
            ->get();
            
        $schoolId = auth()->user()->school_id;
        $imported = 0;

        DB::transaction(function () use ($globalCurricula, $schoolId, &$imported) {
            foreach ($globalCurricula as $gc) {
                $exists = Curriculum::where('school_id', $schoolId)
                    ->where('name', $gc->name)
                    ->exists();
                    
                if (!$exists) {
                    $newCurr = $gc->replicate();
                    $newCurr->school_id = $schoolId;
                    $newCurr->save();
                    
                    // Sync subjects. We need to map global subject IDs to local subject IDs using subject_code
                    $syncData = [];
                    foreach ($gc->subjects as $globalSubject) {
                        // Find local subject with same subject_code
                        $localSubject = Subject::where('school_id', $schoolId)
                            ->where('subject_code', $globalSubject->subject_code)
                            ->first();
                            
                        // Auto-import the subject if it doesn't exist
                        if (!$localSubject) {
                            $localSubject = $globalSubject->replicate();
                            $localSubject->school_id = $schoolId;
                            $localSubject->save();
                        }
                            
                        $syncData[$localSubject->id] = [
                            'weekly_hours' => $globalSubject->pivot->weekly_hours,
                            'sort_order' => $globalSubject->pivot->sort_order,
                        ];
                    }
                    $newCurr->subjects()->sync($syncData);
                    $imported++;
                }
            }
        });

        return redirect()->back()->with('success', "បាននាំចូលកម្មវិធីសិក្សាគំរូចំនួន {$imported} ដោយជោគជ័យ។");
    }
}
