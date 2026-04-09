<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Mark;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarkController extends Controller
{
    public function index()
    {
        $marks = Mark::with(['assessment.course', 'enrollment.student'])
            ->where('user_id', Auth::id())
            ->orderByDesc('id')
            ->get();

        return view('mark.index', compact('marks'));
    }

    public function create()
    {
        $courses = Course::orderBy('course_code')->get();
        $assessments = Assessment::with('course')
            ->orderBy('title')
            ->get();

        return view('mark.create', compact('courses', 'assessments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'assessment_id' => 'required|exists:assessments,id',
            'student_id' => 'required|string|exists:students,student_id',
            'score' => 'required|numeric|min:0|max:999.99',
            'is_locked' => 'nullable|boolean',
        ]);

        $assessment = Assessment::findOrFail($validated['assessment_id']);

        if ((int) $assessment->course_id !== (int) $validated['course_id']) {
            return back()
                ->withErrors(['assessment_id' => 'The selected assessment does not belong to the selected course.'])
                ->withInput();
        }

        $enrollment = $this->resolveEnrollment($validated['student_id'], (int) $validated['course_id']);

        if (! $enrollment) {
            return back()
                ->withErrors(['student_id' => 'No enrollment was found for that student in the selected course.'])
                ->withInput();
        }

        $existingMark = Mark::where('enrollment_id', $enrollment->id)
            ->where('assessment_id', $assessment->id)
            ->first();

        if ($existingMark) {
            return back()
                ->withErrors(['assessment_id' => 'A mark already exists for this student and assessment.'])
                ->withInput();
        }

        Mark::create([
            'enrollment_id' => $enrollment->id,
            'assessment_id' => $assessment->id,
            'user_id' => (int) $request->user()->id,
            'score' => $validated['score'],
            'is_locked' => $request->boolean('is_locked'),
        ]);

        return redirect()->route('marks.index')->with('success', 'Mark entered successfully.');
    }

    public function edit(Mark $mark)
    {
        $this->ensureOwner($mark);

        if ($mark->is_locked) {
            return redirect()->route('marks.index')->with('warning', 'This mark is locked. Use Request Edit to ask for it to be unlocked.');
        }

        $mark->load(['assessment.course', 'enrollment.student']);

        $courses = Course::orderBy('course_code')->get();
        $assessments = Assessment::with('course')
            ->orderBy('title')
            ->get();

        return view('mark.edit', compact('mark', 'courses', 'assessments'));
    }

    public function update(Request $request, Mark $mark): RedirectResponse
    {
        $this->ensureOwner($mark);

        if ($mark->is_locked) {
            return redirect()->route('marks.index')->with('warning', 'Locked marks cannot be edited until they are unlocked.');
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'assessment_id' => 'required|exists:assessments,id',
            'student_id' => 'required|string|exists:students,student_id',
            'score' => 'required|numeric|min:0|max:999.99',
            'is_locked' => 'nullable|boolean',
        ]);

        $assessment = Assessment::findOrFail($validated['assessment_id']);

        if ((int) $assessment->course_id !== (int) $validated['course_id']) {
            return back()
                ->withErrors(['assessment_id' => 'The selected assessment does not belong to the selected course.'])
                ->withInput();
        }

        $enrollment = $this->resolveEnrollment($validated['student_id'], (int) $validated['course_id']);

        if (! $enrollment) {
            return back()
                ->withErrors(['student_id' => 'No enrollment was found for that student in the selected course.'])
                ->withInput();
        }

        $existingMark = Mark::where('enrollment_id', $enrollment->id)
            ->where('assessment_id', $assessment->id)
            ->where('id', '!=', $mark->id)
            ->first();

        if ($existingMark) {
            return back()
                ->withErrors(['assessment_id' => 'A mark already exists for this student and assessment.'])
                ->withInput();
        }

        $mark->update([
            'enrollment_id' => $enrollment->id,
            'assessment_id' => $assessment->id,
            'score' => $validated['score'],
            'is_locked' => $request->boolean('is_locked'),
        ]);

        return redirect()->route('marks.index')->with('success', 'Mark updated successfully.');
    }

    public function destroy(Mark $mark): RedirectResponse
    {
        $this->ensureOwner($mark);

        if ($mark->is_locked) {
            return redirect()->route('marks.index')->with('warning', 'Locked marks cannot be deleted.');
        }

        $mark->delete();

        return redirect()->route('marks.index')->with('success', 'Mark deleted successfully.');
    }

    public function requestEdit(Mark $mark): RedirectResponse
    {
        $this->ensureOwner($mark);

        if (! $mark->is_locked) {
            return redirect()->route('marks.edit', $mark);
        }

        return redirect()->route('marks.index')->with('warning', 'This mark is locked. An administrator needs to unlock it before you can edit it.');
    }

    private function resolveEnrollment(string $studentId, int $courseId): ?Enrollment
    {
        return Enrollment::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->orderByDesc('academic_year_id')
            ->orderByDesc('id')
            ->first();
    }

    private function ensureOwner(Mark $mark): void
    {
        abort_unless((int) $mark->user_id === (int) Auth::id(), 403);
    }
}