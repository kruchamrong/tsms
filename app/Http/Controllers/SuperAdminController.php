<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Validation\Rules;

class SuperAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('school')->where('role', 'school_admin');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('school', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        // Calculate Stats
        $totalUsers = User::where('role', 'school_admin')->count();
        $configuredSchools = User::where('role', 'school_admin')->whereNotNull('school_id')->count();
        $unconfiguredSchools = User::where('role', 'school_admin')->whereNull('school_id')->count();

        return Inertia::render('SuperAdmin/Users/Index', [
            'users' => $users,
            'filters' => $request->only('search'),
            'stats' => [
                'total' => $totalUsers,
                'configured' => $configuredSchools,
                'unconfigured' => $unconfiguredSchools,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:255',
            'password' => ['required', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'school_admin',
            'school_id' => null, // Will be set during onboarding
        ]);

        return redirect()->back()->with('message', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        if ($user->role !== 'school_admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:255',
            'password' => ['nullable', Rules\Password::defaults()],
            'school_name' => 'nullable|string|max:255',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        if ($user->school_id && $request->filled('school_name')) {
            $user->school->name = $request->school_name;
            $user->school->save();
        }

        return redirect()->back()->with('message', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->role === 'school_admin') {
            $user->delete();
        }
        return redirect()->back();
    }
}
