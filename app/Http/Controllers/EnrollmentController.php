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
            ->whereHas('academicYear', function ($query) {
                $query->where('is_current', true);
            })
            ->orderBy('student_id')
            ->paginate(50);

        return view('enrollment.index', compact('enrollments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = Course::orderBy('course_code')->get();
        $academicYears = AcademicYear::where('is_current', true)->orderBy('year')->get();

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

    public function downloadSampleCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="enrollments_sample.csv"',
        ];

        $callback = function () {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['student_id', 'course_code', 'academic_year']);
            fputcsv($output, ['STU001', 'CSC101', '2025/2026']);
            fputcsv($output, ['STU002', 'MTH102', '2025/2026']);

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'enrollments_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $uploadedFile = $request->file('enrollments_file');

        if (! $uploadedFile || ! $uploadedFile->isValid()) {
            return redirect()->route('enrollments.index')->with('error', 'Upload failed. Please try again with a valid CSV file.');
        }

        $handle = fopen($uploadedFile->getPathname(), 'r');

        if (! $handle) {
            return redirect()->route('enrollments.index')->with('error', 'Unable to open the uploaded file.');
        }

        $header = fgetcsv($handle);

        if (! $header) {
            fclose($handle);

            return redirect()->route('enrollments.index')->with('error', 'The uploaded CSV file is empty.');
        }

        $normalizeHeaderKey = function ($column) {
            $clean = strtolower(trim((string) $column));
            $clean = preg_replace('/^\xEF\xBB\xBF/', '', $clean);
            $clean = str_replace(['-', ' '], '_', $clean);

            return $clean;
        };

        $headerAliases = [
            'student_id' => ['student_id', 'studentid', 'student_no', 'student_number'],
            'course_id' => ['course_id'],
            'course_code' => ['course_code', 'course', 'coursecode'],
            'academic_year_id' => ['academic_year_id'],
            'academic_year' => ['academic_year', 'year', 'session'],
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

            return redirect()->route('enrollments.index')->with('error', 'CSV must include a student id header (example: student_id).');
        }

        if (! in_array('course_id', $normalizedHeader, true) && ! in_array('course_code', $normalizedHeader, true)) {
            fclose($handle);

            return redirect()->route('enrollments.index')->with('error', 'CSV must include course_id or course_code.');
        }

        if (! in_array('academic_year_id', $normalizedHeader, true) && ! in_array('academic_year', $normalizedHeader, true)) {
            fclose($handle);

            return redirect()->route('enrollments.index')->with('error', 'CSV must include academic_year_id or academic_year.');
        }

        $studentIdMap = Student::pluck('student_id')->flip()->map(fn ($id) => $id)->all();
        $courseIdMap = Course::pluck('id')->mapWithKeys(fn ($id) => [(string) $id => $id])->all();
        $courseCodeMap = Course::pluck('id', 'course_code')
            ->mapWithKeys(fn ($id, $code) => [strtoupper(trim((string) $code)) => $id])
            ->all();
        $academicYearIdMap = AcademicYear::pluck('id')->mapWithKeys(fn ($id) => [(string) $id => $id])->all();
        $academicYearTextMap = AcademicYear::pluck('id', 'year')
            ->mapWithKeys(fn ($id, $year) => [strtolower(trim((string) $year)) => $id])
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

            if ($studentId === '' || ! isset($studentIdMap[$studentId])) {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: invalid student_id.";
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

            $academicYearId = null;
            if (isset($record['academic_year_id']) && trim((string) $record['academic_year_id']) !== '') {
                $academicYearId = $academicYearIdMap[trim((string) $record['academic_year_id'])] ?? null;
            }
            if (! $academicYearId) {
                $academicYear = strtolower(trim((string) ($record['academic_year'] ?? '')));
                if ($academicYear !== '') {
                    $academicYearId = $academicYearTextMap[$academicYear] ?? null;
                }
            }

            if (! $academicYearId) {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: invalid academic year reference.";
                continue;
            }

            $exists = Enrollment::where('student_id', $studentId)
                ->where('course_id', $courseId)
                ->where('academic_year_id', $academicYearId)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Enrollment::create([
                'student_id' => $studentId,
                'course_id' => $courseId,
                'academic_year_id' => $academicYearId,
            ]);
            $created++;
        }

        fclose($handle);

        $message = "Bulk upload completed. Created: {$created}, Skipped: {$skipped}.";

        if (! empty($rowErrors)) {
            $message .= ' Issues: '.implode(' ', array_slice($rowErrors, 0, 5));
        }

        return redirect()->route('enrollments.index')->with('success', $message);
    }
}