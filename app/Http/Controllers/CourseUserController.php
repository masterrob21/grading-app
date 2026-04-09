<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courseUsers = CourseUser::with(['course', 'user'])
            ->orderByDesc('id')
            ->get();

        return view('course_user.index', compact('courseUsers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = Course::orderBy('course_code')->get();
        $users = User::orderBy('name')->get();

        return view('course_user.create', compact('courses', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => [
                'required',
                'exists:courses,id',
                Rule::unique('course_users')->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                }),
            ],
            'user_id' => 'required|exists:users,id',
        ]);

        CourseUser::create($validated);

        return redirect()->route('course_users.index')->with('success', 'Course user created successfully.');
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
    public function edit(CourseUser $courseUser)
    {
        $courses = Course::orderBy('course_code')->get();
        $users = User::orderBy('name')->get();

        return view('course_user.edit', compact('courseUser', 'courses', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourseUser $courseUser)
    {
        $validated = $request->validate([
            'course_id' => [
                'required',
                'exists:courses,id',
                Rule::unique('course_users')->ignore($courseUser->id)->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                }),
            ],
            'user_id' => 'required|exists:users,id',
        ]);

        $courseUser->update($validated);

        return redirect()->route('course_users.index')->with('success', 'Course user updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseUser $courseUser)
    {
        $courseUser->delete();

        return redirect()->route('course_users.index')->with('success', 'Course user deleted successfully.');
    }
}
