<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherAvailability;
use App\Models\TeacherAvailabilityRemark;
use App\Models\TeacherDocument;
use App\Models\Shift;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeacherAvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $teacherId = $request->query('teacher_id');
        $availabilities = [];
        $remarks = [];
        $documents = [];
        if ($teacherId) {
            $availabilities = TeacherAvailability::where('teacher_id', $teacherId)->get();
            $remarks = TeacherAvailabilityRemark::where('teacher_id', $teacherId)->get();
            $documents = TeacherDocument::where('teacher_id', $teacherId)->latest()->get();
        }
            
        return Inertia::render('TeacherAvailability/Index', [
            'teachers' => Teacher::orderBy('khmer_name')->get(),
            'shifts' => Shift::all(),
            'selectedTeacherId' => $teacherId,
            'availabilities' => $availabilities,
            'remarks' => $remarks,
            'documents' => $documents,
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('Hit store method! Request data: ', $request->all());
        
        try {
            $validated = $request->validate([
                'teacher_id' => [
                    'required',
                    Rule::exists('teachers', 'id')->where(function ($query) {
                        if (auth()->check() && auth()->user()->school_id) {
                            $query->where('school_id', auth()->user()->school_id);
                        }
                    }),
                ],
                'availabilities' => 'array',
                'availabilities.*.day_of_week' => 'required|integer|min:1|max:7',
                'availabilities.*.shift_id' => [
                    'required',
                    Rule::exists('shifts', 'id')->where(function ($query) {
                        if (auth()->check() && auth()->user()->school_id) {
                            $query->where(function ($q) {
                                $q->where('school_id', auth()->user()->school_id)
                                  ->orWhereNull('school_id');
                            });
                        }
                    }),
                ],
                'availabilities.*.is_available' => 'boolean',
                'remarks' => 'array',
                'remarks.*.day_of_week' => 'required|integer|min:1|max:7',
                'remarks.*.remarks' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('TeacherAvailability validation failed:', $e->errors());
            throw $e;
        }

        DB::transaction(function () use ($validated) {
            TeacherAvailability::where('teacher_id', $validated['teacher_id'])->delete();

            \Log::info('Validated availabilities count: ' . (isset($validated['availabilities']) ? count($validated['availabilities']) : 'none'));

            if (!empty($validated['availabilities'])) {
                $schoolId = auth()->check() ? auth()->user()->school_id : null;
                $insertData = collect($validated['availabilities'])->map(function ($item) use ($validated, $schoolId) {
                    return [
                        'id' => (string) Str::uuid(),
                        'school_id' => $schoolId,
                        'teacher_id' => $validated['teacher_id'],
                        'day_of_week' => $item['day_of_week'],
                        'shift_id' => $item['shift_id'],
                        'is_available' => $item['is_available'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();
                
                if (count($insertData) > 0) {
                    TeacherAvailability::insert($insertData);
                }
            }
            
            TeacherAvailabilityRemark::where('teacher_id', $validated['teacher_id'])->delete();
            
            if (!empty($validated['remarks'])) {
                $schoolId = auth()->check() ? auth()->user()->school_id : null;
                $insertRemarks = collect($validated['remarks'])->filter(function($item) {
                    return !empty($item['remarks']);
                })->map(function ($item) use ($validated, $schoolId) {
                    return [
                        'id' => (string) Str::uuid(),
                        'school_id' => $schoolId,
                        'teacher_id' => $validated['teacher_id'],
                        'day_of_week' => $item['day_of_week'],
                        'remarks' => $item['remarks'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();
                
                if (count($insertRemarks) > 0) {
                    TeacherAvailabilityRemark::insert($insertRemarks);
                }
            }
        });

        return redirect()->back();
    }
}
