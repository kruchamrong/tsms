<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Teacher::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('khmer_name', 'like', "%{$search}%")
                  ->orWhere('english_name', 'like', "%{$search}%")
                  ->orWhere('teacher_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $teachers = $query->orderBy('khmer_name', 'asc')->paginate(10)->withQueryString();

        return Inertia::render('Teacher/Index', [
            'teachers' => $teachers,
            'filters' => $request->only(['search', 'employment_type', 'gender']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Teacher/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request)
    {
        Teacher::create($request->validated());

        return redirect()->route('teachers.index')->with('success', 'គ្រូបង្រៀនត្រូវបានបន្ថែមដោយជោគជ័យ។');
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher)
    {
        return Inertia::render('Teacher/Show', [
            'teacher' => $teacher
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher)
    {
        return Inertia::render('Teacher/Edit', [
            'teacher' => $teacher
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $data = $request->validated();
        
        if (isset($data['remove_photo']) && $data['remove_photo']) {
            if ($teacher->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($teacher->photo);
            }
            $data['photo'] = null;
        } elseif ($request->hasFile('photo')) {
            if ($teacher->photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($teacher->photo);
            }
            $data['photo'] = $request->file('photo')->store('teachers', 'public');
        }

        unset($data['remove_photo']);

        $teacher->update($data);

        return back()->with('success', 'គ្រូបង្រៀនត្រូវបានកែប្រែដោយជោគជ័យ។');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        $teacher->delete();

        return redirect()->back()->with('success', 'គ្រូបង្រៀនត្រូវបានលុបដោយជោគជ័យ។');
    }

    public function truncate()
    {
        Teacher::query()->delete();
        
        return redirect()->back()->with('success', 'ទិន្នន័យគ្រូបង្រៀនទាំងអស់ត្រូវបានលុបដោយជោគជ័យ។');
    }

    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=teachers_template.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['teacher_code', 'khmer_name', 'english_name', 'gender', 'employment_type', 'phone'];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility with Khmer characters
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, $columns);
            
            // Add a sample row
            fputcsv($file, ['T001', 'សុខ សាន្ត', 'Sokh San', 'M', 'Full-Time', '012345678']);
            fputcsv($file, ['T002', 'ចាន់ ធីតា', 'Chan Thida', 'F', 'Part-Time', '098765432']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
        // Put the pointer back to start and parse as CSV, or we can just skip the first line assuming it's header.
        // Actually, let's just use fgetcsv for the first line and check for BOM just in case.
        rewind($handle);
        $header = fgetcsv($handle);
        if ($header !== false && isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }
        
        $imported = 0;
        $errors = [];
        $rowNum = 1; // 1 was header

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Ensure we have at least 1 column for code
                if (count($row) >= 1) {
                    $code = trim($row[0] ?? '');
                    
                    if (empty($code)) {
                        $errors[] = "ជួរទី $rowNum: មិនមានអត្តលេខ";
                        continue;
                    }

                    if (Teacher::withTrashed()->where('teacher_code', $code)->exists()) {
                        $errors[] = "ជួរទី $rowNum: អត្តលេខ '$code' មានរួចហើយ";
                        continue;
                    }

                    try {
                        $genderInput = trim($row[3] ?? '');
                        $gender = (strtoupper($genderInput) === 'F' || $genderInput === 'ស្រី' || $genderInput === 'នារី') ? 'F' : 'M';
                        
                        $typeInput = trim($row[4] ?? '');
                        $type = (strtoupper($typeInput) === 'PART-TIME' || $typeInput === 'ក្រៅម៉ោង') ? 'Part-Time' : 'Full-Time';

                        Teacher::create([
                            'teacher_code' => $code,
                            'khmer_name' => trim($row[1] ?? ''),
                            'english_name' => trim($row[2] ?? ''),
                            'gender' => $gender,
                            'employment_type' => $type,
                            'phone' => trim($row[5] ?? ''),
                        ]);
                        $imported++;
                    } catch (\Illuminate\Database\QueryException $e) {
                        $errors[] = "ជួរទី $rowNum: បញ្ហាទិន្នន័យ (Database Error)";
                        continue;
                    } catch (\Exception $e) {
                        $errors[] = "ជួរទី $rowNum: បញ្ហាមិនស្គាល់";
                        continue;
                    }
                } else {
                    $errors[] = "ជួរទី $rowNum: ទម្រង់ទិន្នន័យមិនត្រឹមត្រូវ";
                }
            }
            fclose($handle);

            if (count($errors) > 0) {
                DB::rollBack();
                $errorSummary = implode(', ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $errorSummary .= " និង " . (count($errors) - 5) . " បញ្ហាផ្សេងទៀត...";
                }
                return redirect()->back()->with('error', "បរាជ័យក្នុងការនាំចូល! សូមកែតម្រូវទិន្នន័យក្នុងឯកសារសិន។ មានបញ្ហា៖ " . $errorSummary);
            }

            DB::commit();
            $message = "បាននាំចូលទិន្នន័យជោគជ័យចំនួន $imported នាក់។";
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return redirect()->back()->with('error', "មានកំហុសប្រព័ន្ធ (System Error) កំឡុងពេលនាំចូលទិន្នន័យ។");
        }
    }

    public function importPaste(Request $request)
    {
        $request->validate([
            'data' => 'required|string',
        ]);

        $pastedData = trim($request->data);
        $rows = explode("\n", $pastedData);
        
        $imported = 0;
        $errors = [];
        $rowNum = 0;

        DB::beginTransaction();

        try {
            foreach ($rows as $line) {
                $rowNum++;
                $line = trim($line);
                if (empty($line)) continue;

                // Split by tab (Excel copy-paste uses tabs)
                $columns = explode("\t", $line);
                
                // Usually copied data from Excel might include a header row if user selected it
                // We skip if it looks like a header (e.g. contains 'teacher_code' or 'អត្តលេខ')
                if ($rowNum === 1 && (stripos($columns[0] ?? '', 'teacher_code') !== false || stripos($columns[0] ?? '', 'អត្តលេខ') !== false)) {
                    continue;
                }

                if (count($columns) >= 1) {
                    $code = trim($columns[0] ?? '');
                    
                    if (empty($code)) {
                        $errors[] = "ជួរទី $rowNum: មិនមានអត្តលេខ";
                        continue;
                    }

                    if (Teacher::withTrashed()->where('teacher_code', $code)->exists()) {
                        $errors[] = "ជួរទី $rowNum: អត្តលេខ '$code' មានរួចហើយ";
                        continue;
                    }

                    try {
                        $genderInput = trim($columns[3] ?? '');
                        $gender = (strtoupper($genderInput) === 'F' || $genderInput === 'ស្រី' || $genderInput === 'នារី') ? 'F' : 'M';
                        
                        $typeInput = trim($columns[4] ?? '');
                        $type = (strtoupper($typeInput) === 'PART-TIME' || $typeInput === 'ក្រៅម៉ោង') ? 'Part-Time' : 'Full-Time';

                        Teacher::create([
                            'teacher_code' => $code,
                            'khmer_name' => trim($columns[1] ?? ''),
                            'english_name' => trim($columns[2] ?? ''),
                            'gender' => $gender,
                            'employment_type' => $type,
                            'phone' => trim($columns[5] ?? ''),
                        ]);
                        $imported++;
                    } catch (\Illuminate\Database\QueryException $e) {
                        $errors[] = "ជួរទី $rowNum: បញ្ហាទិន្នន័យ (Database Error)";
                        continue;
                    } catch (\Exception $e) {
                        $errors[] = "ជួរទី $rowNum: បញ្ហាមិនស្គាល់";
                        continue;
                    }
                } else {
                    $errors[] = "ជួរទី $rowNum: ទម្រង់ទិន្នន័យមិនត្រឹមត្រូវ";
                }
            }

            if (count($errors) > 0) {
                DB::rollBack();
                $errorSummary = implode(', ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $errorSummary .= " និង " . (count($errors) - 5) . " បញ្ហាផ្សេងទៀត...";
                }
                return redirect()->back()->with('error', "បរាជ័យក្នុងការនាំចូល! មានបញ្ហា៖ " . $errorSummary);
            }

            DB::commit();
            $message = "បាននាំចូលទិន្នន័យជោគជ័យចំនួន $imported នាក់។";
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', "មានកំហុសប្រព័ន្ធ (System Error) កំឡុងពេលនាំចូលទិន្នន័យ។");
        }
    }
}
