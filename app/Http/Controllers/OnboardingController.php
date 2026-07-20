<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\School;
use App\Models\Subject;
use App\Models\SubjectGroup;
use App\Models\Curriculum;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    public function index()
    {
        if (auth()->user()->school_id) {
            return redirect()->route('dashboard');
        }
        return Inertia::render('Onboarding/Index');
    }

    public function store(Request $request)
    {
        if (auth()->user()->school_id) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'education_level' => 'required|in:អនុវិទ្យាល័យ,វិទ្យាល័យ,អនុវិទ្យាល័យ និងវិទ្យាល័យ',
            'principal_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create School
            $schoolData = $request->only('name', 'education_level', 'principal_name', 'phone', 'address');
            $schoolData['trial_ends_at'] = now()->addDays(14);
            $school = School::create($schoolData);

            // Update User
            $user = auth()->user();
            $user->school_id = $school->id;
            $user->save();

            // Clone Template Data (where school_id is null)
            // Disable global scope temporarily or just run raw queries
            $newSchoolId = $school->id;

            // Clone SubjectGroups
            $groups = DB::table('subject_groups')->whereNull('school_id')->get();
            $groupMap = [];
            foreach ($groups as $group) {
                $newGroupId = DB::table('subject_groups')->insertGetId([
                    'name' => $group->name,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'school_id' => $newSchoolId
                ]);
                $groupMap[$group->id] = $newGroupId;
            }

            // Clone Subjects
            $subjects = DB::table('subjects')->whereNull('school_id')->get();
            $subjectMap = [];
            foreach ($subjects as $subject) {
                $newSubjectId = DB::table('subjects')->insertGetId([
                    'subject_code' => $subject->subject_code,
                    'khmer_name' => $subject->khmer_name,
                    'english_name' => $subject->english_name,
                    'short_name' => $subject->short_name,
                    'color' => $subject->color,
                    'subject_group_id' => $subject->subject_group_id ? ($groupMap[$subject->subject_group_id] ?? null) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'school_id' => $newSchoolId
                ]);
                $subjectMap[$subject->id] = $newSubjectId;
            }

            // Clone Curriculums
            $curriculaQuery = DB::table('curricula')->whereNull('school_id');
            
            if ($school->education_level === 'អនុវិទ្យាល័យ') {
                $curriculaQuery->whereIn('name', ['ថ្នាក់ទី៧', 'ថ្នាក់ទី៨', 'ថ្នាក់ទី៩']);
            } elseif ($school->education_level === 'វិទ្យាល័យ') {
                $curriculaQuery->whereIn('name', ['ថ្នាក់ទី១០', 'ថ្នាក់ទី១១-វិទ្យាសាស្ត្រសង្គម', 'ថ្នាក់ទី១១-វិទ្យាសាស្ត្រពិត', 'ថ្នាក់ទី១២-វិទ្យាសាស្ត្រសង្គម', 'ថ្នាក់ទី១២-វិទ្យាសាស្ត្រពិត']);
            }
            
            $curriculums = $curriculaQuery->get();
            
            foreach ($curriculums as $curriculum) {
                $newCurriculumId = DB::table('curricula')->insertGetId([
                    'name' => $curriculum->name,
                    'description' => $curriculum->description,
                    'sort_order' => $curriculum->sort_order,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'school_id' => $newSchoolId
                ]);

                // Clone curriculum_subject pivots
                $pivots = DB::table('curriculum_subject')->where('curriculum_id', $curriculum->id)->get();
                foreach ($pivots as $pivot) {
                    if (isset($subjectMap[$pivot->subject_id])) {
                        DB::table('curriculum_subject')->insert([
                            'curriculum_id' => $newCurriculumId,
                            'subject_id' => $subjectMap[$pivot->subject_id],
                            'weekly_hours' => $pivot->weekly_hours,
                            'sort_order' => $pivot->sort_order,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'មានបញ្ហាក្នុងការបង្កើតសាលា៖ ' . $e->getMessage()]);
        }
    }
}
