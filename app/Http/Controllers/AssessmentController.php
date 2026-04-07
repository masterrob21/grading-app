<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Course;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assessments = Assessment::with('course')
            ->orderBy('title')
            ->get();

        return view('assessment.index', compact('assessments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = Course::orderBy('course_code')->get();

        return view('assessment.create', compact('courses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'max_score' => 'required|numeric|min:0.01|max:999.99',
            'weight' => 'required|numeric|min:0|max:100',
        ]);

        Assessment::create($validated);

        return redirect()->route('assessments.index')->with('success', 'Assessment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Assessment $assessment)
    {
        $assessment->load('course');

        return view('assessment.show', compact('assessment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Assessment $assessment)
    {
        $courses = Course::orderBy('course_code')->get();

        return view('assessment.edit', compact('assessment', 'courses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Assessment $assessment)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'max_score' => 'required|numeric|min:0.01|max:999.99',
            'weight' => 'required|numeric|min:0|max:100',
        ]);

        $assessment->update($validated);

        return redirect()->route('assessments.index')->with('success', 'Assessment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Assessment $assessment)
    {
        $assessment->delete();

        return redirect()->route('assessments.index')->with('success', 'Assessment deleted successfully.');
    }
}