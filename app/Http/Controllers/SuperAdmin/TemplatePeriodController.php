<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Period;
use App\Models\Shift;
use Inertia\Inertia;

class TemplatePeriodController extends Controller
{
    public function index(Request $request)
    {
        $query = Period::whereNull('school_id')->with('shift');

        if ($request->has('search') && $request->search != '') {
            $query->whereHas('shift', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $periods = $query->orderBy('shift_id')->orderBy('start_time')->paginate(50)->withQueryString();
        $shifts = Shift::whereNull('school_id')->get();

        return Inertia::render('SuperAdmin/Templates/Periods/Index', [
            'periods' => $periods,
            'shifts' => $shifts,
            'filters' => $request->only('search')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_time' => 'required',
            'end_time' => 'required',
            'shift_id' => 'required|exists:shifts,id'
        ]);

        Period::create([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'shift_id' => $request->shift_id,
            'school_id' => null
        ]);

        return redirect()->back()->with('message', 'បង្កើតម៉ោងសិក្សាគំរូបានជោគជ័យ។');
    }

    public function update(Request $request, Period $template_period)
    {
        if ($template_period->school_id !== null) {
            abort(403);
        }

        $request->validate([
            'start_time' => 'required',
            'end_time' => 'required',
            'shift_id' => 'required|exists:shifts,id'
        ]);

        $template_period->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'shift_id' => $request->shift_id
        ]);

        return redirect()->back()->with('message', 'កែប្រែម៉ោងសិក្សាគំរូបានជោគជ័យ។');
    }

    public function destroy(Period $template_period)
    {
        if ($template_period->school_id !== null) {
            abort(403);
        }

        $template_period->delete();

        return redirect()->back()->with('message', 'លុបម៉ោងសិក្សាគំរូបានជោគជ័យ។');
    }
}
