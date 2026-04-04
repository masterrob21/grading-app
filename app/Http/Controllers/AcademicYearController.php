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
            'is_current' => 'sometimes|boolean',
        ]);

        $validated['is_current'] = $request->has('is_current');

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
            'is_current' => 'sometimes|boolean',
        ]);

        $validated['is_current'] = $request->has('is_current');

        $academicYear->update($validated);

        return redirect()->route('academic_years.index')->with('success', 'Academic year updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();

        return redirect()->route('academic_years.index')->with('success', 'Academic year deleted successfully.');
    }
}