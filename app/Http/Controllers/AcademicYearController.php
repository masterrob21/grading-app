<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $academicYears = AcademicYear::orderByDesc('is_current')->orderBy('year')->get();

        return view('academic_year.index', compact('academicYears'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('academic_year.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|string|max:10|unique:academic_years,year',
            'is_current' => 'nullable|boolean',
        ]);

        $validated['is_current'] = $request->has('is_current');

        if ($validated['is_current'] && $this->hasAnotherActiveAcademicYear()) {
            return back()
                ->withErrors(['is_current' => 'Only one academic year can be active at a time.'])
                ->withInput();
        }

        AcademicYear::create($validated);

        return redirect()->route('academic_years.index')->with('success', 'Academic year created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Not implemented.
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AcademicYear $academicYear)
    {
        return view('academic_year.edit', compact('academicYear'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'year' => 'required|string|max:10|unique:academic_years,year,' . $academicYear->id,
            'is_current' => 'nullable|boolean',
        ]);

        $validated['is_current'] = $request->has('is_current');

        if ($validated['is_current'] && $this->hasAnotherActiveAcademicYear($academicYear->id)) {
            return back()
                ->withErrors(['is_current' => 'Only one academic year can be active at a time.'])
                ->withInput();
        }

        $academicYear->update($validated);

        return redirect()->route('academic_years.index')->with('success', 'Academic year updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicYear $academicYear)
    {
        if ($academicYear->enrollments()->count() > 0) {
            return redirect()->route('academic_years.index')->with('error', 'Cannot delete academic year. It has associated enrollments.');
        }

        $academicYear->delete();

        return redirect()->route('academic_years.index')->with('success', 'Academic year deleted successfully.');
    }

    private function hasAnotherActiveAcademicYear(?int $ignoreId = null): bool
    {
        return AcademicYear::where('is_current', true)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}