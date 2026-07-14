<?php

namespace App\Http\Controllers;

use App\Models\TeachingAssignment;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Services\TeachingAssignmentService;
use App\Http\Requests\StoreTeachingAssignmentRequest;
use App\Http\Requests\ReassignTeachingAssignmentRequest;

class TeachingAssignmentController extends Controller
{
    protected $assignmentService;

    public function __construct(TeachingAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    public function index(Request $request)
    {
        $data = $this->assignmentService->getAssignmentData($request);
        return Inertia::render('TeachingAssignment/Index', $data);
    }

    public function store(StoreTeachingAssignmentRequest $request)
    {
        $this->assignmentService->storeAssignment($request->validated());

        return redirect()->back()->with('success', 'ការចាត់តាំងត្រូវបានរក្សាទុកដោយជោគជ័យ។');
    }

    public function checkClassStatus(Request $request)
    {
        $result = $this->assignmentService->checkClassStatus($request->query('class_id'));
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        return response()->json($result['data']);
    }

    public function destroy(TeachingAssignment $teachingAssignment)
    {
        \App\Models\TeachingAssignment::disableAuditing();
        $teachingAssignment->delete();
        \App\Models\TeachingAssignment::enableAuditing();
        return redirect()->back()->with('success', 'ការចាត់តាំងត្រូវបានលុបដោយជោគជ័យ។');
    }

    public function reassign(ReassignTeachingAssignmentRequest $request)
    {
        $this->assignmentService->reassignAssignment($request->validated());

        return redirect()->back()->with('success', 'ការចាត់តាំងត្រូវបានផ្លាស់ប្តូរដោយជោគជ័យ។');
    }

    public function destroyGroup(Request $request, Teacher $teacher, Subject $subject)
    {
        $this->assignmentService->destroyGroup($teacher, $subject);

        return redirect()->back()->with('success', 'ម៉ោងបង្រៀនត្រូវបានលុបដោយជោគជ័យ។');
    }

    public function truncate()
    {
        $this->assignmentService->truncateAssignments();
        
        return redirect()->back()->with('success', 'ម៉ោងបង្រៀនទាំងអស់ត្រូវបានលុបដោយជោគជ័យ។');
    }
}
