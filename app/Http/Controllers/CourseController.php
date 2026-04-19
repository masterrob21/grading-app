<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courses = Course::with('user')
            ->orderBy('course_code')->get();
        return view('course.index', compact('courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::orderBy('name')->get();
        
        return view('course.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_code' => 'required|string|unique:courses|max:50',
            'title' => 'required|string|max:255',
            'semester' => 'required|integer|in:1,2',
            'user_id' => 'required|exists:users,id',
        ]);

        Course::create($validated);

        return redirect()->route('courses.index')->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        $users = User::orderBy('name')->get();

        return view('course.edit', compact('course', 'users'));
      
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'course_code' => 'required|string|unique:courses,course_code,' . $course->id . '|max:50',
            'title' => 'required|string|max:255',
            'semester' => 'required|integer|in:1,2',
            'user_id' => 'required|exists:users,id',
        ]);

        $course->update($validated);

        return redirect()->route('courses.index')->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        if ($course->enrollments()->count() > 0 || $course->assessments()->count() > 0) {
            return redirect()->route('courses.index')->with('error', 'Cannot delete course. It has associated enrollments or assessments.');
        }
        
        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course deleted successfully.');
    }
}