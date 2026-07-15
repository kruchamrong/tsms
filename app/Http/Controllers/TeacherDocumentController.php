<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeacherDocumentController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'teacher_id' => [
                    'required',
                    Rule::exists('teachers', 'id')->where(function ($query) {
                        if (auth()->check() && auth()->user()->school_id) {
                            $query->where('school_id', auth()->user()->school_id);
                        }
                    }),
                ],
                'title' => 'required|string|max:255',
                'document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:102400', // Max 100MB
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->hasFile('document') && !$request->file('document')->isValid()) {
                \Log::error('File upload error code: ' . $request->file('document')->getError());
            }
            \Log::error('Upload validation failed:', $e->errors());
            throw $e;
        }

        $file = $request->file('document');
        $originalName = $file->getClientOriginalName();
        $extension = $file->extension() ?: $file->getClientOriginalExtension();
        
        $path = $file->storeAs(
            'private/teacher_documents/' . $request->teacher_id,
            Str::uuid() . '.' . $extension
        );

        TeacherDocument::create([
            'teacher_id' => $request->teacher_id,
            'title' => $request->title,
            'file_path' => $path,
            'file_type' => $extension,
            'original_name' => $originalName,
        ]);

        return redirect()->back()->with('success', 'Document uploaded successfully.');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $document = TeacherDocument::findOrFail($id);
        $document->update([
            'title' => $request->title,
        ]);

        return redirect()->back()->with('success', 'Document title updated successfully.');
    }

    public function download($id)
    {
        $document = TeacherDocument::findOrFail($id);
        
        // TODO: Ensure the user is authorized to download this file (e.g., auth()->user())
        
        if (!Storage::exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::download($document->file_path, $document->original_name);
    }

    public function show($id)
    {
        $document = TeacherDocument::findOrFail($id);
        
        if (!Storage::exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::response($document->file_path);
    }

    public function destroy($id)
    {
        $document = TeacherDocument::findOrFail($id);
        
        if (Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }
        
        $document->delete();

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }
}
