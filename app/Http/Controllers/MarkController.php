<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
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

        $marksQuery = $this->currentAcademicYearMarksQuery();

        $marks = (clone $marksQuery)
            ->with(['assessment.course', 'enrollment.student'])
            ->when($courseId, fn ($query) => $query->whereHas('assessment', fn ($q) => $q->where('course_id', $courseId)))
            ->when($assessmentId, fn ($query) => $query->where('assessment_id', $assessmentId))
            ->orderBy(
                Enrollment::select('student_id')
                    ->whereColumn('enrollments.id', 'marks.enrollment_id')
            )
            ->get();

        $courses = (clone $marksQuery)
            ->with('assessment.course')
            ->get()
            ->pluck('assessment.course')
            ->unique('id')
            ->sortBy('course_code')
            ->values();

        $assessments = $courseId
            ? (clone $marksQuery)
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
        $currentAcademicYearId = $this->currentAcademicYearId();
        $assignedCourseIds = CourseUser::where('user_id', Auth::id())
            ->pluck('course_id');

        $courses = Course::whereIn('id', $assignedCourseIds)
            ->when(
                $currentAcademicYearId,
                fn ($query) => $query->whereHas('enrollments', fn ($enrollmentQuery) => $enrollmentQuery->where('academic_year_id', $currentAcademicYearId)),
                fn ($query) => $query->whereRaw('0 = 1')
            )
            ->orderBy('course_code')
            ->get();

        $assessments = Assessment::with('course')
            ->whereIn('course_id', $courses->pluck('id'))
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

        if ((float) $validated['score'] > (float) $assessment->max_score) {
            return back()
                ->withErrors(['score' => 'The score cannot be greater than the assessment maximum score.'])
                ->withInput();
        }

        $enrollment = $this->resolveCurrentAcademicYearEnrollment($validated['student_id'], (int) $validated['course_id']);

        if (! $enrollment) {
            return back()
                ->withErrors(['student_id' => 'No current academic year enrollment was found for that student in the selected course.'])
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

        if ((float) $validated['score'] > (float) $assessment->max_score) {
            return back()
                ->withErrors(['score' => 'The score cannot be greater than the assessment maximum score.'])
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

        return redirect()->route('marks.index')->with('warning', 'This mark is locked. The course lead needs to unlock it before you can edit it.');
    }

    public function lock(Mark $mark): RedirectResponse
    {
        $this->ensureCourseOwner($mark);

        if ($mark->is_locked) {
            return redirect()->route('marksheet.index')->with('warning', 'This mark is already locked.');
        }

        $mark->update([
            'is_locked' => true,
        ]);

        return redirect()->route('marksheet.index')->with('success', 'Mark locked successfully.');
    }

    public function bulkLock(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mark_ids' => 'required|array|min:1',
            'mark_ids.*' => 'required|integer|exists:marks,id',
        ]);

        $selectedIds = collect($validated['mark_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $eligibleMarks = Mark::with('enrollment.course')
            ->whereIn('id', $selectedIds)
            ->get()
            ->filter(fn (Mark $mark) => (int) ($mark->enrollment?->course?->user_id ?? 0) === (int) Auth::id())
            ->values();

        $lockableIds = $eligibleMarks
            ->filter(fn (Mark $mark) => ! $mark->is_locked)
            ->pluck('id')
            ->all();

        if (! empty($lockableIds)) {
            Mark::whereIn('id', $lockableIds)->update([
                'is_locked' => true,
            ]);
        }

        $lockedCount = count($lockableIds);
        $alreadyLockedCount = $eligibleMarks->count() - $lockedCount;
        $unauthorizedCount = $selectedIds->count() - $eligibleMarks->count();

        $message = "Bulk lock completed. Locked: {$lockedCount}.";

        if ($alreadyLockedCount > 0) {
            $message .= " Already locked: {$alreadyLockedCount}.";
        }

        if ($unauthorizedCount > 0) {
            $message .= " Skipped (outside your courses): {$unauthorizedCount}.";
        }

        return redirect()->route('marksheet.index')->with($lockedCount > 0 ? 'success' : 'warning', $message);
    }

    public function unlock(Mark $mark): RedirectResponse
    {
        $this->ensureCourseOwner($mark);

        if (! $mark->is_locked) {
            return redirect()->route('marksheet.index')->with('warning', 'This mark is already editable.');
        }

        $mark->update([
            'is_locked' => false,
        ]);

        return redirect()->route('marksheet.index')->with('success', 'Mark unlocked successfully.');
    }

    public function bulkUnlock(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mark_ids' => 'required|array|min:1',
            'mark_ids.*' => 'required|integer|exists:marks,id',
        ]);

        $selectedIds = collect($validated['mark_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $eligibleMarks = Mark::with('enrollment.course')
            ->whereIn('id', $selectedIds)
            ->get()
            ->filter(fn (Mark $mark) => (int) ($mark->enrollment?->course?->user_id ?? 0) === (int) Auth::id())
            ->values();

        $unlockableIds = $eligibleMarks
            ->filter(fn (Mark $mark) => $mark->is_locked)
            ->pluck('id')
            ->all();

        if (! empty($unlockableIds)) {
            Mark::whereIn('id', $unlockableIds)->update([
                'is_locked' => false,
            ]);
        }

        $unlockedCount = count($unlockableIds);
        $alreadyEditableCount = $eligibleMarks->count() - $unlockedCount;
        $unauthorizedCount = $selectedIds->count() - $eligibleMarks->count();

        $message = "Bulk unlock completed. Unlocked: {$unlockedCount}.";

        if ($alreadyEditableCount > 0) {
            $message .= " Already editable: {$alreadyEditableCount}.";
        }

        if ($unauthorizedCount > 0) {
            $message .= " Skipped (outside your courses): {$unauthorizedCount}.";
        }

        return redirect()->route('marksheet.index')->with($unlockedCount > 0 ? 'success' : 'warning', $message);
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

    private function ensureCourseOwner(Mark $mark): void
    {
        $mark->loadMissing('enrollment.course');

        abort_unless((int) ($mark->enrollment?->course?->user_id ?? 0) === (int) Auth::id(), 403);
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

        $assessments = Assessment::whereIn('course_id', $assignedCourseIds)
            ->get(['id', 'course_id', 'title', 'max_score']);
        $assessmentTitleByCourseMap = $assessments
            ->mapWithKeys(fn ($assessment) => [
                ((string) $assessment->course_id).'|'.strtolower(trim((string) $assessment->title)) => (int) $assessment->id,
            ])
            ->all();
        $assessmentIdMap = $assessments
            ->mapWithKeys(fn ($assessment) => [(string) $assessment->id => (int) $assessment->id])
            ->all();
        $assessmentCourseMap = $assessments
            ->mapWithKeys(fn ($assessment) => [(string) $assessment->id => (int) $assessment->course_id])
            ->all();
        $assessmentMaxScoreMap = $assessments
            ->mapWithKeys(fn ($assessment) => [(string) $assessment->id => (float) $assessment->max_score])
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
                    $assessmentKey = ((string) $courseId).'|'.$assessmentTitle;
                    $assessmentId = $assessmentTitleByCourseMap[$assessmentKey] ?? null;
                }
            }

            if (! $assessmentId) {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: invalid assessment reference.";

                continue;
            }

            if ((int) ($assessmentCourseMap[(string) $assessmentId] ?? 0) !== (int) $courseId) {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: assessment does not belong to the selected course.";

                continue;
            }

            $assessmentMaxScore = $assessmentMaxScoreMap[(string) $assessmentId] ?? null;

            if ($assessmentMaxScore === null) {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: assessment maximum score is unavailable.";

                continue;
            }

            if ((float) $score > (float) $assessmentMaxScore) {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: score cannot be greater than assessment maximum score ({$assessmentMaxScore}).";

                continue;
            }

            $enrollment = $this->resolveCurrentAcademicYearEnrollment($studentId, $courseId);

            if (! $enrollment) {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: no current academic year enrollment found for student in course.";

                continue;
            }

            $existingMark = Mark::where('enrollment_id', $enrollment->id)
                ->where('assessment_id', $assessmentId)
                ->first();

            if ($existingMark) {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: mark already exists for this student enrollment and assessment.";

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

        return redirect()->route('marks.index')->with('status', $message);
    }

    public function exportCsv(Request $request)
    {
        $courseId = $request->query('course_id');
        $assessmentId = $request->query('assessment_id');

        $marks = $this->currentAcademicYearMarksQuery()
            ->with(['assessment.course', 'enrollment.student'])
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

    private function currentAcademicYearId(): ?int
    {
        return AcademicYear::where('is_current', true)->value('id');
    }

    private function currentAcademicYearMarksQuery()
    {
        $currentAcademicYearId = $this->currentAcademicYearId();

        return Mark::query()
            ->where('user_id', Auth::id())
            ->when(
                $currentAcademicYearId,
                fn ($query) => $query->whereHas('enrollment', fn ($enrollmentQuery) => $enrollmentQuery->where('academic_year_id', $currentAcademicYearId)),
                fn ($query) => $query->whereRaw('0 = 1')
            );
    }

    private function resolveCurrentAcademicYearEnrollment(string $studentId, int $courseId): ?Enrollment
    {
        $currentAcademicYearId = $this->currentAcademicYearId();

        if (! $currentAcademicYearId) {
            return null;
        }

        return Enrollment::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->where('academic_year_id', $currentAcademicYearId)
            ->orderByDesc('id')
            ->first();
    }

    public function exportMarksheetCsv(Request $request)
    {
        $courseId = $request->query('course_id');
        $assessmentId = $request->query('assessment_id');
        $studentId = trim((string) $request->query('student_id', ''));
        $userIds = Course::where('user_id', Auth::id())->pluck('id')->all();

        $marks = Mark::with(['assessment.course', 'enrollment.student', 'user'])
        
            ->when($courseId, fn ($query) => $query->whereHas('assessment', fn ($q) => $q->where('course_id', $courseId)))
            ->when($assessmentId, fn ($query) => $query->where('assessment_id', $assessmentId))
            ->when($studentId !== '', fn ($query) => $query->whereHas('enrollment.student', fn ($q) => $q->where('student_id', 'like', '%'.$studentId.'%')))
            ->orderBy(
                Enrollment::select('student_id')
                    ->whereColumn('enrollments.id', 'marks.enrollment_id')
            )
            ->get();

        $rows = $this->prepareExportRows($marks);
        $filename = 'marksheet_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['Student ID', 'User Name', 'Course Code', 'Course Title', 'Assessment', 'Score']);

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
        $studentId = trim((string) $request->query('student_id', ''));

        $currentAcademicYearId = $this->currentAcademicYearId();

        $marksheetBaseQuery = Mark::query()
            ->whereHas('enrollment.course', fn ($query) => $query->where('user_id', Auth::id()))
            ->when(
                $currentAcademicYearId,
                fn ($query) => $query->whereHas('enrollment', fn ($enrollmentQuery) => $enrollmentQuery->where('academic_year_id', $currentAcademicYearId)),
                fn ($query) => $query->whereRaw('0 = 1')
            );

        $marks = (clone $marksheetBaseQuery)
            ->with(['assessment.course', 'enrollment.student', 'enrollment.course', 'user'])
            ->when($courseId, fn ($query) => $query->whereHas('assessment', fn ($q) => $q->where('course_id', $courseId)))
            ->when($assessmentId, fn ($query) => $query->where('assessment_id', $assessmentId))
            ->when($studentId !== '', fn ($query) => $query->whereHas('enrollment.student', fn ($q) => $q->where('student_id', 'like', '%'.$studentId.'%')))
            ->orderBy(
                Enrollment::select('student_id')
                    ->whereColumn('enrollments.id', 'marks.enrollment_id')
            )
            ->get();

        $courses = (clone $marksheetBaseQuery)
            ->with('assessment.course')
            ->get()
            ->pluck('assessment.course')
            ->unique('id')
            ->sortBy('course_code')
            ->values();
    
        $assessments = $courseId
            ? (clone $marksheetBaseQuery)
                ->with('assessment')
                ->whereHas('assessment', fn ($q) => $q->where('course_id', $courseId))
                ->get()
                ->pluck('assessment')
                ->unique('id')
                ->sortBy('title')
                ->values()
            : collect();

        return view('marksheet.index', compact('marks', 'courses', 'assessments', 'courseId', 'assessmentId', 'studentId'));
    }

    private function prepareExportRows(Collection $marks): array
    {
        return $marks->map(function (Mark $mark) {
            $course = $mark->assessment?->course;

            return [
                $mark->enrollment?->student?->student_id ?? '-',
                $mark->user?->name ?? '-',
                $course?->course_code ?? '-',
                $course?->title ?? '-',
                $mark->assessment?->title ?? '-',
                number_format((float) $mark->score, 2, '.', ''),
            ];
        })->all();
    }
}