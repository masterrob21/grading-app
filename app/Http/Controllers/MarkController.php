<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseUser;
use App\Models\Enrollment;
use App\Models\Mark;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class MarkController extends Controller
{
    public function index(Request $request)
    {
        $courseId = $request->query('course_id');
        $assessmentId = $request->query('assessment_id');

        $marks = Mark::with(['assessment.course', 'enrollment.student'])
            ->where('user_id', Auth::id())
            ->when($courseId, fn ($query) => $query->whereHas('assessment', fn ($q) => $q->where('course_id', $courseId)))
            ->when($assessmentId, fn ($query) => $query->where('assessment_id', $assessmentId))
            ->orderBy(
                Enrollment::select('student_id')
                    ->whereColumn('enrollments.id', 'marks.enrollment_id')
            )
            ->get();

        $courses = Mark::where('user_id', Auth::id())
            ->with('assessment.course')
            ->get()
            ->pluck('assessment.course')
            ->unique('id')
            ->sortBy('course_code')
            ->values();

        $assessments = $courseId
            ? Mark::where('user_id', Auth::id())
                ->with('assessment')
                ->whereHas('assessment', fn ($q) => $q->where('course_id', $courseId))
                ->get()
                ->pluck('assessment')
                ->unique('id')
                ->sortBy('title')
                ->values()
            : collect();

        return view('mark.index', compact('marks', 'courses', 'assessments', 'courseId', 'assessmentId'));
    }

    public function create()
    {
        $assignedCourseIds = CourseUser::where('user_id', Auth::id())
            ->pluck('course_id');

        $courses = Course::whereIn('id', $assignedCourseIds)
            ->orderBy('course_code')
            ->get();

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

    public function downloadSampleCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="marks_sample.csv"',
        ];

        $callback = function () {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['student_id', 'course_code', 'assessment_title', 'score']);
            fputcsv($output, ['STU001', 'CSC101', 'Midterm Exam', '85.50']);
            fputcsv($output, ['STU002', 'MTH102', 'Final Exam', '92.75']);

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkUpload(Request $request): RedirectResponse
    {
        $request->validate([
            'marks_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $uploadedFile = $request->file('marks_file');

        if (! $uploadedFile || ! $uploadedFile->isValid()) {
            return redirect()->route('marks.index')->with('error', 'Upload failed. Please try again with a valid CSV file.');
        }

        $handle = fopen($uploadedFile->getPathname(), 'r');

        if (! $handle) {
            return redirect()->route('marks.index')->with('error', 'Unable to open the uploaded file.');
        }

        $header = fgetcsv($handle);

        if (! $header) {
            fclose($handle);

            return redirect()->route('marks.index')->with('error', 'The uploaded CSV file is empty.');
        }

        $normalizeHeaderKey = function ($column) {
            $clean = strtolower(trim((string) $column));
            $clean = preg_replace('/^\xEF\xBB\xBF/', '', $clean);
            $clean = str_replace(['-', ' '], '_', $clean);

            return $clean;
        };

        $headerAliases = [
            'student_id' => ['student_id', 'studentid', 'student_no'],
            'course_code' => ['course_code', 'course', 'coursecode'],
            'course_id' => ['course_id'],
            'assessment_title' => ['assessment_title', 'assessment', 'title'],
            'assessment_id' => ['assessment_id'],
            'score' => ['score', 'mark', 'marks'],
        ];

        $normalizedHeader = array_map(function ($column) use ($normalizeHeaderKey, $headerAliases) {
            $normalized = $normalizeHeaderKey($column);

            foreach ($headerAliases as $canonical => $aliases) {
                if (in_array($normalized, $aliases, true)) {
                    return $canonical;
                }
            }

            return $normalized;
        }, $header);

        if (! in_array('student_id', $normalizedHeader, true)) {
            fclose($handle);

            return redirect()->route('marks.index')->with('error', 'CSV must include student_id.');
        }

        if (! in_array('score', $normalizedHeader, true)) {
            fclose($handle);

            return redirect()->route('marks.index')->with('error', 'CSV must include score.');
        }

        if (! in_array('course_code', $normalizedHeader, true) && ! in_array('course_id', $normalizedHeader, true)) {
            fclose($handle);

            return redirect()->route('marks.index')->with('error', 'CSV must include course_code or course_id.');
        }

        if (! in_array('assessment_title', $normalizedHeader, true) && ! in_array('assessment_id', $normalizedHeader, true)) {
            fclose($handle);

            return redirect()->route('marks.index')->with('error', 'CSV must include assessment_title or assessment_id.');
        }

        $assignedCourseIds = CourseUser::where('user_id', Auth::id())->pluck('course_id')->all();
        $courseCodeMap = Course::whereIn('id', $assignedCourseIds)->pluck('id', 'course_code')
            ->mapWithKeys(fn ($id, $code) => [strtoupper(trim((string) $code)) => $id])
            ->all();
        $courseIdMap = Course::whereIn('id', $assignedCourseIds)->pluck('id')
            ->mapWithKeys(fn ($id) => [(string) $id => $id])
            ->all();

        $assessmentTitleMap = Assessment::whereIn('course_id', $assignedCourseIds)->pluck('id', 'title')
            ->mapWithKeys(fn ($id, $title) => [strtolower(trim((string) $title)) => $id])
            ->all();
        $assessmentIdMap = Assessment::whereIn('course_id', $assignedCourseIds)->pluck('id')
            ->mapWithKeys(fn ($id) => [(string) $id => $id])
            ->all();

        $created = 0;
        $skipped = 0;
        $lineNumber = 1;
        $rowErrors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (! array_filter($row, fn ($value) => trim((string) $value) !== '')) {
                continue;
            }

            $rowData = array_pad($row, count($normalizedHeader), null);
            $record = array_combine($normalizedHeader, $rowData);

            $studentId = trim((string) ($record['student_id'] ?? ''));
            $score = trim((string) ($record['score'] ?? ''));

            if ($studentId === '' || $score === '') {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: missing student_id or score.";

                continue;
            }

            if (! is_numeric($score)) {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: score must be numeric.";

                continue;
            }

            $courseId = null;
            if (isset($record['course_id']) && trim((string) $record['course_id']) !== '') {
                $courseId = $courseIdMap[trim((string) $record['course_id'])] ?? null;
            }
            if (! $courseId) {
                $courseCode = strtoupper(trim((string) ($record['course_code'] ?? '')));
                if ($courseCode !== '') {
                    $courseId = $courseCodeMap[$courseCode] ?? null;
                }
            }

            if (! $courseId) {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: invalid course reference.";

                continue;
            }

            $assessmentId = null;
            if (isset($record['assessment_id']) && trim((string) $record['assessment_id']) !== '') {
                $assessmentId = $assessmentIdMap[trim((string) $record['assessment_id'])] ?? null;
            }
            if (! $assessmentId) {
                $assessmentTitle = strtolower(trim((string) ($record['assessment_title'] ?? '')));
                if ($assessmentTitle !== '') {
                    $assessmentId = $assessmentTitleMap[$assessmentTitle] ?? null;
                }
            }

            if (! $assessmentId) {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: invalid assessment reference.";

                continue;
            }

            $enrollment = $this->resolveEnrollment($studentId, $courseId);

            if (! $enrollment) {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: no enrollment found for student in course.";

                continue;
            }

            $existingMark = Mark::where('enrollment_id', $enrollment->id)
                ->where('assessment_id', $assessmentId)
                ->first();

            if ($existingMark) {
                $skipped++;

                continue;
            }

            Mark::create([
                'enrollment_id' => $enrollment->id,
                'assessment_id' => $assessmentId,
                'user_id' => (int) Auth::id(),
                'score' => (float) $score,
                'is_locked' => false,
            ]);
            $created++;
        }

        fclose($handle);

        $message = "Bulk upload completed. Created: {$created}, Skipped: {$skipped}.";

        if (! empty($rowErrors)) {
            $message .= ' Issues: '.implode(' ', array_slice($rowErrors, 0, 5));
        }

        return redirect()->route('marks.index')->with('success', $message);
    }

    public function exportCsv(Request $request)
    {
        $courseId = $request->query('course_id');
        $assessmentId = $request->query('assessment_id');

        $marks = Mark::with(['assessment.course', 'enrollment.student'])
            ->where('user_id', Auth::id())
            ->when($courseId, fn ($query) => $query->whereHas('assessment', fn ($q) => $q->where('course_id', $courseId)))
            ->when($assessmentId, fn ($query) => $query->where('assessment_id', $assessmentId))
            ->orderBy(
                Enrollment::select('student_id')
                    ->whereColumn('enrollments.id', 'marks.enrollment_id')
            )
            ->get();

        $rows = $this->prepareExportRows($marks);
        $filename = 'marks_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['Student ID', 'Course Code', 'Course Title', 'Assessment', 'Score']);

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportMarksheetCsv(Request $request)
    {
        $courseId = $request->query('course_id');
        $assessmentId = $request->query('assessment_id');
        $userIds = Course::where('user_id', Auth::id())->pluck('id')->all();

        $marks = Mark::with(['assessment.course', 'enrollment.student'])
            ->where('user_id', $userIds)
            ->when($courseId, fn ($query) => $query->whereHas('assessment', fn ($q) => $q->where('course_id', $courseId)))
            ->when($assessmentId, fn ($query) => $query->where('assessment_id', $assessmentId))
            ->orderBy(
                Enrollment::select('student_id')
                    ->whereColumn('enrollments.id', 'marks.enrollment_id')
            )
            ->get();

        $rows = $this->prepareExportRows($marks);
        $filename = 'marksheet_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['Student ID', 'Course Code', 'Course Title', 'Assessment', 'Score']);

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function showMarksheet(Request $request)
    {
        $courseId = $request->query('course_id');
        $assessmentId = $request->query('assessment_id');
        $userId = Course::where('user_id', Auth::id())->pluck('id')->all();

        $marks = Mark::with(['assessment.course', 'enrollment.student'])
            ->where('user_id', $userId)
            ->when($courseId, fn ($query) => $query->whereHas('assessment', fn ($q) => $q->where('course_id', $courseId)))
            ->when($assessmentId, fn ($query) => $query->where('assessment_id', $assessmentId))
            ->orderBy(
                Enrollment::select('student_id')
                    ->whereColumn('enrollments.id', 'marks.enrollment_id')
            )
            ->get();

        $courses = Mark::where('user_id', $userId)
            ->with('assessment.course')
            ->get()
            ->pluck('assessment.course')
            ->unique('id')
            ->sortBy('course_code')
            ->values();

        $assessments = $courseId
            ? Mark::where('user_id', $userId)
                ->with('assessment')
                ->whereHas('assessment', fn ($q) => $q->where('course_id', $courseId))
                ->get()
                ->pluck('assessment')
                ->unique('id')
                ->sortBy('title')
                ->values()
            : collect();

        return view('marksheet.index', compact('marks', 'courses', 'assessments', 'courseId', 'assessmentId'));
    }

    private function prepareExportRows(Collection $marks): array
    {
        return $marks->map(function (Mark $mark) {
            $course = $mark->assessment?->course;

            return [
                $mark->enrollment?->student?->student_id ?? '-',
                $course?->course_code ?? '-',
                $course?->title ?? '-',
                $mark->assessment?->title ?? '-',
                number_format((float) $mark->score, 2, '.', ''),
            ];
        })->all();
    }
}
