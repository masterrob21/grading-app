<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $enrollments = Enrollment::with(['student', 'course', 'academicYear'])
            ->orderBy('id', 'desc')
            ->get();

        return view('enrollment.index', compact('enrollments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = Course::orderBy('course_code')->get();
        $academicYears = AcademicYear::orderBy('year')->get();

        return view('enrollment.create', compact('courses', 'academicYears'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                'string',
                'exists:students,student_id',
                Rule::unique('enrollments')->where(function ($query) use ($request) {
                    return $query->where('course_id', $request->course_id)
                        ->where('academic_year_id', $request->academic_year_id);
                }),
            ],
            'course_id' => 'required|exists:courses,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        Enrollment::create($validated);

        return redirect()->route('enrollments.index')->with('success', 'Enrollment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Enrollment $enrollment)
    {
        $enrollment->load(['student', 'course', 'academicYear']);

        return view('enrollment.show', compact('enrollment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enrollment $enrollment)
    {
        $courses = Course::orderBy('course_code')->get();
        $academicYears = AcademicYear::orderBy('year')->get();

        return view('enrollment.edit', compact('enrollment', 'courses', 'academicYears'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                'string',
                'exists:students,student_id',
                Rule::unique('enrollments')->ignore($enrollment->id)->where(function ($query) use ($request) {
                    return $query->where('course_id', $request->course_id)
                        ->where('academic_year_id', $request->academic_year_id);
                }),
            ],
            'course_id' => 'required|exists:courses,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $enrollment->update($validated);

        return redirect()->route('enrollments.index')->with('success', 'Enrollment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();

        return redirect()->route('enrollments.index')->with('success', 'Enrollment deleted successfully.');
    }
}
