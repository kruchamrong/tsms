<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $schools = School::withCount('users')
            ->with(['users' => function($query) { $query->where('role', 'school_admin')->oldest(); }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($school) {
                $admin = $school->users->first();
                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'created_at' => $school->created_at,
                    'trial_ends_at' => $school->trial_ends_at,
                    'is_active' => $school->onTrial(),
                    'users_count' => $school->users_count,
                    'contact_name' => $school->principal_name ?: ($admin ? $admin->name : 'N/A'),
                    'phone' => $school->phone
                ];
            });

        return Inertia::render('SuperAdmin/Schools/Index', [
            'schools' => $schools
        ]);
    }

    public function extendValidity(Request $request, School $school)
    {
        $request->validate([
            'days' => 'required|integer|min:1',
        ]);

        $currentEnd = $school->trial_ends_at ? Carbon::parse($school->trial_ends_at) : now();
        
        // If the school is already expired, we add days from today. Otherwise, we add to the current end date.
        if ($currentEnd->isPast()) {
            $school->trial_ends_at = now()->addDays($request->days);
        } else {
            $school->trial_ends_at = $currentEnd->addDays($request->days);
        }

        $school->save();

        return redirect()->back()->with('success', 'បានបន្ថែមសុពលភាព ' . $request->days . ' ថ្ងៃដោយជោគជ័យ!');
    }
}
