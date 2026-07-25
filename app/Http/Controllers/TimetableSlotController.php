<?php

namespace App\Http\Controllers;

use App\Models\TimetableSlot;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\TimetableService;
use App\Http\Requests\ToggleTimetableSlotRequest;
use App\Http\Requests\SwapTimetableSlotRequest;

class TimetableSlotController extends Controller
{
    protected $timetableService;

    public function __construct(TimetableService $timetableService)
    {
        $this->timetableService = $timetableService;
    }

    public function index(Request $request)
    {
        $data = $this->timetableService->getTimetableData($request);
        return Inertia::render('Timetable/Index', $data);
    }

    public function toggle(ToggleTimetableSlotRequest $request)
    {
        $result = $this->timetableService->toggleSlot($request->validated());

        if (isset($result['type'])) {
            return redirect()->back()->with($result['type'], $result['message']);
        }
        
        return redirect()->back();
    }

    public function swap(SwapTimetableSlotRequest $request)
    {
        $result = $this->timetableService->swapSlot($request->validated());

        if (isset($result['type']) && $result['type'] !== 'none') {
            return redirect()->back()->with($result['type'], $result['message']);
        }
        
        return redirect()->back();
    }

    public function destroy($id)
    {
        $slot = TimetableSlot::findOrFail($id);
        $slot->delete();
        return redirect()->back()->with('success', 'បានបញ្ជូនម៉ោងទៅកាន់ឃ្លាំងផ្អាករួចរាល់។');
    }

    public function truncate()
    {
        $this->timetableService->truncateSlots();
        return redirect()->back()->with('success', 'កាលវិភាគទាំងអស់ត្រូវបានលុបដោយជោគជ័យ។');
    }
}
