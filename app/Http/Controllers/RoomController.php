<?php
namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoomController extends Controller {
    
    public function index(Request $request) {
        $query = Room::with('schoolClasses');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('room_name', 'like', "%{$search}%");
        }

        if ($request->filled('building')) {
            $query->where('building', $request->building);
        }

        $sort = $request->input('sort', 'room_name');
        $direction = $request->input('direction', 'asc');
        
        // Ensure valid sort column
        if (!in_array($sort, ['room_name', 'building', 'capacity'])) {
            $sort = 'room_name';
        }

        $rooms = $query->orderBy($sort, $direction)->paginate(10)->withQueryString();

        $allClasses = SchoolClass::with('grade')->get();
        $buildings = Room::whereNotNull('building')->where('building', '!=', '')->distinct()->orderBy('building')->pluck('building');
        $roomTypes = Room::whereNotNull('room_type')->where('room_type', '!=', '')->distinct()->orderBy('room_type')->pluck('room_type');

        return Inertia::render('Room/Index', [
            'rooms' => $rooms,
            'allClasses' => $allClasses,
            'buildings' => $buildings,
            'roomTypes' => $roomTypes,
            'filters' => $request->only(['search', 'building', 'sort', 'direction'])
        ]);
    }
    
    public function create() {
        $roomTypes = Room::whereNotNull('room_type')->where('room_type', '!=', '')->distinct()->orderBy('room_type')->pluck('room_type');
        return Inertia::render('Room/Create', [
            'roomTypes' => $roomTypes
        ]);
    }
    
    public function store(Request $request) {
        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where(function($q) {
                    $q->where('school_id', auth()->user()->school_id)
                      ->orWhereNull('school_id');
                });
            }
        };

        $validated = $request->validate([
            'room_name' => ['required', 'string', Rule::unique('rooms')->where($schoolIdCheck)],
            'building' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'room_type' => 'required|string'
        ]);

        Room::create($validated);
        
        return redirect()->route('rooms.index')->with('success', 'បន្ទប់រៀនត្រូវបានបន្ថែមដោយជោគជ័យ។');
    }
    
    public function edit(Room $room) {
        $roomTypes = Room::whereNotNull('room_type')->where('room_type', '!=', '')->distinct()->orderBy('room_type')->pluck('room_type');
        return Inertia::render('Room/Edit', [
            'room' => $room,
            'roomTypes' => $roomTypes
        ]);
    }
    
    public function update(Request $request, Room $room) {
        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where(function($q) {
                    $q->where('school_id', auth()->user()->school_id)
                      ->orWhereNull('school_id');
                });
            }
        };

        $validated = $request->validate([
            'room_name' => ['required', 'string', Rule::unique('rooms', 'room_name')->ignore($room->id)->where($schoolIdCheck)],
            'building' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'room_type' => 'required|string'
        ]);

        $room->update($validated);
        
        return back()->with('success', 'បន្ទប់រៀនត្រូវបានកែប្រែដោយជោគជ័យ។');
    }
    
    public function destroy(Room $room) {
        $room->delete();
        return redirect()->route('rooms.index')->with('success', 'បន្ទប់រៀនត្រូវបានលុបដោយជោគជ័យ។');
    }

    public function assignClasses(Request $request, Room $room) {
        $schoolIdCheck = function ($query) {
            if (auth()->check() && auth()->user()->school_id) {
                return $query->where(function($q) {
                    $q->where('school_id', auth()->user()->school_id)
                      ->orWhereNull('school_id');
                });
            }
        };

        $request->validate([
            'class_ids' => 'array',
            'class_ids.*' => [Rule::exists('school_classes', 'id')->where($schoolIdCheck)],
        ]);

        DB::transaction(function () use ($room, $request) {
            // Remove old classes from this room
            SchoolClass::where('room_id', $room->id)->update(['room_id' => null]);

            // Assign new classes
            if (!empty($request->class_ids)) {
                SchoolClass::whereIn('id', $request->class_ids)->update(['room_id' => $room->id]);
            }
        });

        return back()->with('success', 'ថ្នាក់រៀនត្រូវបានកំណត់ចូលបន្ទប់ដោយជោគជ័យ។');
    }
}
