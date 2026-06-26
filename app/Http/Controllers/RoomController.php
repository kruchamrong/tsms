<?php
namespace App\Http\Controllers;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoomController extends Controller {
    public function index() {
        return Inertia::render('Room/Index', ['rooms' => Room::latest()->paginate(10)]);
    }
    public function create() {
        return Inertia::render('Room/Create');
    }
    public function store(Request $request) {
        Room::create($request->validate([
            'room_name' => 'required|string|unique:rooms',
            'capacity' => 'required|integer',
            'room_type' => 'required|string'
        ]));
        return redirect()->route('rooms.index');
    }
    public function edit(Room $room) {
        return Inertia::render('Room/Edit', ['room' => $room]);
    }
    public function update(Request $request, Room $room) {
        $room->update($request->validate([
            'room_name' => 'required|string|unique:rooms,room_name,'.$room->id,
            'capacity' => 'required|integer',
            'room_type' => 'required|string'
        ]));
        return redirect()->route('rooms.index');
    }
    public function destroy(Room $room) {
        $room->delete();
        return redirect()->route('rooms.index');
    }
}
