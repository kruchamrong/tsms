<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class SchoolProfileController extends Controller
{
    /**
     * Show the form for editing the school profile.
     */
    public function edit(Request $request)
    {
        $school = $request->user()->school;

        // Provinces list for the dropdown
        $provinces = [
            'រាជធានីភ្នំពេញ',
            'ខេត្តកណ្តាល',
            'ខេត្តតាកែវ',
            'ខេត្តកំពង់ស្ពឺ',
            'ខេត្តកំពង់ឆ្នាំង',
            'ខេត្តកំពង់ចាម',
            'ខេត្តត្បូងឃ្មុំ',
            'ខេត្តព្រៃវែង',
            'ខេត្តស្វាយរៀង',
            'ខេត្តកំពង់ធំ',
            'ខេត្តសៀមរាប',
            'ខេត្តបន្ទាយមានជ័យ',
            'ខេត្តបាត់ដំបង',
            'ខេត្តពោធិ៍សាត់',
            'ខេត្តប៉ៃលិន',
            'ខេត្តឧត្តរមានជ័យ',
            'ខេត្តព្រះវិហារ',
            'ខេត្តស្ទឹងត្រែង',
            'ខេត្តក្រចេះ',
            'ខេត្តរតនគិរី',
            'ខេត្តមណ្ឌលគិរី',
            'ខេត្តព្រះសីហនុ',
            'ខេត្តកំពត',
            'ខេត្តកែប',
            'ខេត្តកោះកុង',
        ];

        return Inertia::render('SchoolProfile/Edit', [
            'school' => $school,
            'provinces' => $provinces,
        ]);
    }

    /**
     * Update the school profile.
     */
    public function update(Request $request)
    {
        $school = $request->user()->school;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'province' => 'nullable|string|max:255',
            // Allow other fields if needed later
            // 'principal_name' => 'nullable|string|max:255',
            // 'phone' => 'nullable|string|max:255',
            // 'address' => 'nullable|string|max:255',
        ]);

        $school->update($validated);

        return back()->with('success', 'ព័ត៌មានទូទៅសាលាត្រូវបានរក្សាទុកដោយជោគជ័យ។');
    }
}
