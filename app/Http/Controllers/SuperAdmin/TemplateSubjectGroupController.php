<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubjectGroup;
use Inertia\Inertia;

class TemplateSubjectGroupController extends Controller
{
    public function index(Request $request)
    {
        $query = SubjectGroup::whereNull('school_id');

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $groups = $query->orderBy('name')->paginate(50)->withQueryString();

        return Inertia::render('SuperAdmin/Templates/SubjectGroups/Index', [
            'groups' => $groups,
            'filters' => $request->only('search')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        SubjectGroup::create([
            'name' => $request->name,
            'school_id' => null
        ]);

        return redirect()->back()->with('message', 'បង្កើតកម្រងមុខវិជ្ជាគំរូបានជោគជ័យ។');
    }

    public function update(Request $request, SubjectGroup $template_subject_group)
    {
        if ($template_subject_group->school_id !== null) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $template_subject_group->update([
            'name' => $request->name
        ]);

        return redirect()->back()->with('message', 'កែប្រែកម្រងមុខវិជ្ជាគំរូបានជោគជ័យ។');
    }

    public function destroy(SubjectGroup $template_subject_group)
    {
        if ($template_subject_group->school_id !== null) {
            abort(403);
        }

        $template_subject_group->delete();

        return redirect()->back()->with('message', 'លុបកម្រងមុខវិជ្ជាគំរូបានជោគជ័យ។');
    }
}
