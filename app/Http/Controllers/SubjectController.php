<?php
namespace App\Http\Controllers;
use App\Models\Subject;
use App\Models\SubjectGroup;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SubjectController extends Controller {
    public function index(Request $request) {
        $query = Subject::query();

        if (is_null(auth()->user()->school_id)) {
            $query->whereNull('school_id');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('khmer_name', 'like', "%{$search}%")
                  ->orWhere('english_name', 'like', "%{$search}%")
                  ->orWhere('short_name', 'like', "%{$search}%")
                  ->orWhere('subject_code', 'like', "%{$search}%");
            });
        }
        $subjects = $query->orderBy('subject_code', 'asc')->get();

        return Inertia::render('Subject/Index', [
            'subjects' => $subjects,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create() {
        return Inertia::render('Subject/Create', ['subjectGroups' => SubjectGroup::all()]);
    }

    public function store(Request $request) {
        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where('school_id', auth()->user()->school_id);
            }
        };

        Subject::create($request->validate([
            'subject_code' => ['required', 'string', Rule::unique('subjects')->where($schoolIdCheck)],
            'khmer_name' => 'required|string',
            'english_name' => 'required|string',
            'short_name' => 'nullable|string|max:10',
            'subject_group_id' => ['nullable', Rule::exists('subject_groups', 'id')->where($schoolIdCheck)]
        ]));
        return redirect()->route('subjects.index')->with('success', 'មុខវិជ្ជាត្រូវបានបន្ថែមដោយជោគជ័យ។');
    }

    public function edit(Subject $subject) {
        return Inertia::render('Subject/Edit', ['subject' => $subject, 'subjectGroups' => SubjectGroup::all()]);
    }

    public function update(Request $request, Subject $subject) {
        if ($subject->school_id === null && auth()->user()->role !== 'super_admin') {
            abort(403, 'មិនអាចកែប្រែមុខវិជ្ជាគំរូបានទេ!');
        }

        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where('school_id', auth()->user()->school_id);
            }
        };

        $subject->update($request->validate([
            'subject_code' => ['required', 'string', Rule::unique('subjects', 'subject_code')->ignore($subject->id)->where($schoolIdCheck)],
            'khmer_name' => 'required|string',
            'english_name' => 'required|string',
            'short_name' => 'nullable|string|max:10',
            'subject_group_id' => ['nullable', Rule::exists('subject_groups', 'id')->where($schoolIdCheck)]
        ]));
        return redirect()->route('subjects.index')->with('success', 'មុខវិជ្ជាត្រូវបានកែប្រែដោយជោគជ័យ។');
    }

    public function destroy(Subject $subject) {
        if ($subject->school_id === null && auth()->user()->role !== 'super_admin') {
            abort(403, 'មិនអាចលុបមុខវិជ្ជាគំរូបានទេ!');
        }
        $subject->delete();
        return redirect()->route('subjects.index')->with('success', 'មុខវិជ្ជាត្រូវបានលុបដោយជោគជ័យ។');
    }

    public function updateColor(Request $request, Subject $subject) {
        if ($subject->school_id === null && auth()->user()->role !== 'super_admin') {
            abort(403, 'មិនអាចកែប្រែពណ៌មុខវិជ្ជាគំរូបានទេ!');
        }

        $request->validate([
            'color' => 'nullable|string|max:20'
        ]);
        $subject->update(['color' => $request->color]);
        return redirect()->back();
    }

    public function importPaste(Request $request)
    {
        $request->validate([
            'data' => 'required|string',
        ]);

        $data = $request->input('data');
        $rows = explode("\n", $data);

        $imported = 0;
        $errors = [];
        $rowNumber = 0;
        
        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where('school_id', auth()->user()->school_id);
            }
        };

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $rowStr) {
                $rowNumber++;
                
                $rowStr = trim($rowStr);
                if (empty($rowStr)) continue;

                $row = explode("\t", $rowStr);
                
                if (count($row) < 3) {
                    if ($rowNumber === 1) continue; // Skip header
                    $errors[] = "ជួរទី $rowNumber: ទិន្នន័យមិនគ្រប់គ្រាន់ (ត្រូវមានយ៉ាងហោចណាស់៣ជួរឈរ)";
                    continue;
                }

                $code = trim($row[0]);
                $khmerName = trim($row[1]);
                $englishName = trim($row[2]);
                $shortName = isset($row[3]) ? trim($row[3]) : null;

                if (empty($code)) {
                    if ($rowNumber === 1) continue;
                    $errors[] = "ជួរទី $rowNumber: លេខកូដទទេ";
                    continue;
                }

                if ($rowNumber === 1 && (str_contains(strtolower($code), 'code') || str_contains(strtolower($code), 'លេខកូដ') || str_contains(strtolower($code), 'អត្តលេខ'))) {
                    continue;
                }

                if (Subject::where('subject_code', $code)->where($schoolIdCheck)->exists()) {
                    $errors[] = "ជួរទី $rowNumber: លេខកូដ '$code' មានរួចហើយ";
                    continue;
                }

                try {
                    Subject::create([
                        'subject_code' => $code,
                        'khmer_name' => $khmerName,
                        'english_name' => $englishName,
                        'short_name' => $shortName,
                        'school_id' => auth()->user()->school_id
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "ជួរទី $rowNumber: បរាជ័យក្នុងការបញ្ចូល";
                    continue;
                }
            }

            if (count($errors) > 0) {
                DB::rollBack();
                $errorMsg = "បរាជ័យក្នុងការនាំចូល! មានបញ្ហា " . count($errors) . " ជួរ៖\n" . implode("\n", array_slice($errors, 0, 10)) . (count($errors) > 10 ? "\nនិង " . (count($errors) - 10) . " បញ្ហាទៀត..." : "");
                session()->flash('error', $errorMsg);
                return redirect()->back();
            }

            DB::commit();
            session()->flash('success', "នាំចូលបាន $imported មុខវិជ្ជាដោយជោគជ័យ។");
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', "មានកំហុសប្រព័ន្ធ (System Error) កំឡុងពេលនាំចូលទិន្នន័យ។");
            return redirect()->back();
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->path(), 'r');
        
        // Remove BOM from the first line if present
        $firstLine = fgets($handle);
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);
        // We assume the first line is header, so we skip processing it as data.
        // Even if we read it with fgets, we just ignore it.
        
        $imported = 0;
        $errors = [];
        $rowNumber = 1; // 1 was header

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                if (count($row) >= 4) {
                    $code = trim($row[0]);
                    
                    if (empty($code)) {
                        $errors[] = "ជួរទី $rowNumber: លេខកូដទទេ";
                        continue;
                    }

                    if (Subject::where('subject_code', $code)->exists()) {
                        $errors[] = "ជួរទី $rowNumber: លេខកូដ ($code) មានរួចហើយ";
                        continue;
                    }

                    try {
                        Subject::create([
                            'subject_code' => $code,
                            'khmer_name' => mb_convert_encoding(trim($row[1] ?? ''), 'UTF-8', 'auto'),
                            'english_name' => trim($row[2] ?? ''),
                            'weekly_hours' => (int) trim($row[3] ?? '2'),
                        ]);
                        $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "ជួរទី $rowNumber: បរាជ័យក្នុងការបញ្ចូល";
                        continue;
                    }
                } else {
                    $errors[] = "ជួរទី $rowNumber: ទិន្នន័យមិនគ្រប់គ្រាន់";
                }
            }
            fclose($handle);

            if (count($errors) > 0) {
                DB::rollBack();
                session()->flash('error', "បរាជ័យក្នុងការនាំចូល! មានបញ្ហា " . count($errors) . " ជួរ៖\n" . implode("\n", array_slice($errors, 0, 5)) . (count($errors) > 5 ? "\n..." : ""));
                return redirect()->back();
            }

            DB::commit();
            session()->flash('success', "នាំចូលបាន $imported មុខវិជ្ជាដោយជោគជ័យ។");
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            session()->flash('error', "មានកំហុសប្រព័ន្ធ (System Error) កំឡុងពេលនាំចូលទិន្នន័យ។");
            return redirect()->back();
        }
    }
    public function reorder(Request $request)
    {
        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                // Only allow reordering subjects that BELONG strictly to this school
                return $query->where('school_id', auth()->user()->school_id);
            }
        };

        $request->validate([
            'subject_ids' => 'required|array',
            'subject_ids.*' => [Rule::exists('subjects', 'id')->where($schoolIdCheck)],
        ]);

        $ids = $request->input('subject_ids');
        
        DB::transaction(function () use ($ids) {
            $offset = 0;

            // Pass 1: Set temporary codes to avoid unique constraint violation
            foreach ($ids as $index => $id) {
                $tmpCode = 'TMP-S' . str_pad($offset + $index + 1, 2, '0', STR_PAD_LEFT);
                Subject::where('id', $id)->update(['subject_code' => $tmpCode]);
            }

            // Pass 2: Set actual codes
            foreach ($ids as $index => $id) {
                $code = 'S' . str_pad($offset + $index + 1, 2, '0', STR_PAD_LEFT);
                Subject::where('id', $id)->update(['subject_code' => $code]);
            }
        });

        return redirect()->back()->with('success', 'មុខវិជ្ជាត្រូវបានរៀបចំឡើងវិញជោគជ័យ។ (Subjects reordered successfully.)');
    }
}
