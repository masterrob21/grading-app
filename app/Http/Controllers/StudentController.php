<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $students = Student::with('department')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('student_id', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('student_id')
            ->paginate(50)
            ->withQueryString();

        return view('student.index', compact('students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::orderBy('department_name')->get();

        return view('student.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|string|unique:students|max:50',
            'full_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        Student::create($validated);

        return redirect()->route('students.index')->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        $student->load('department');

        return view('student.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        $departments = Department::orderBy('department_name')->get();

        return view('student.edit', compact('student', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_id' => 'required|string|unique:students,student_id,' . $student->id . '|max:50',
            'full_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        $student->update($validated);

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }

    public function showByStudentId($student_id)
    {
        $student = Student::where('student_id', $student_id)->first();

        if ($student) {
            return response()->json(['name' => 'Valid ID']);//or $student->full_name
        }

        return response()->json(['error' => 'Student not found'], 404);
    }

    public function downloadSampleCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_sample.csv"',
        ];

        $callback = function () {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['student_id', 'full_name', 'department']);
            fputcsv($output, ['STU001', 'Jane Doe', 'Computer Science']);
            fputcsv($output, ['STU002', 'John Smith', 'Mathematics']);

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'students_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $uploadedFile = $request->file('students_file');

        if (! $uploadedFile || ! $uploadedFile->isValid()) {
            return redirect()->route('students.index')->with('error', 'Upload failed. Please try again with a valid CSV file.');
        }

        $filePath = $uploadedFile->getPathname();
        $handle = fopen($filePath, 'r');

        if (! $handle) {
            return redirect()->route('students.index')->with('error', 'Unable to open the uploaded file.');
        }

        $header = fgetcsv($handle);

        if (! $header) {
            fclose($handle);

            return redirect()->route('students.index')->with('error', 'The uploaded CSV file is empty.');
        }

        $normalizeHeaderKey = function ($column) {
            $clean = strtolower(trim((string) $column));
            $clean = preg_replace('/^\xEF\xBB\xBF/', '', $clean);
            $clean = str_replace(['-', ' '], '_', $clean);

            return $clean;
        };

        $headerAliases = [
            'student_id' => ['student_id', 'studentid', 'student_no', 'student_number', 'matric_no', 'matric_number'],
            'full_name' => ['full_name', 'fullname', 'name', 'student_name'],
            'department_id' => ['department_id', 'dept_id'],
            'department_name' => ['department_name', 'department', 'dept_name', 'dept'],
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

        if (! in_array('student_id', $normalizedHeader, true) || ! in_array('full_name', $normalizedHeader, true)) {
            fclose($handle);

            return redirect()->route('students.index')->with('error', 'CSV must include headers for student id and full name (examples: student_id/full_name or Student ID/Full Name).');
        }

        if (
            ! in_array('department_id', $normalizedHeader, true)
            && ! in_array('department_name', $normalizedHeader, true)
        ) {
            fclose($handle);

            return redirect()->route('students.index')->with('error', 'CSV must include one department header: department, department_name, or department_id.');
        }

        $departments = Department::select('id', 'department_name')->get();
        $departmentNameMap = [];
        $departmentIdMap = [];

        foreach ($departments as $department) {
            $departmentNameMap[strtolower(trim($department->department_name))] = $department->id;
            $departmentIdMap[(string) $department->id] = $department->id;
        }

        $created = 0;
        $updated = 0;
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
            $fullName = trim((string) ($record['full_name'] ?? ''));

            if ($studentId === '' || $fullName === '') {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: missing student_id or full_name.";
                continue;
            }

            $departmentId = null;

            if (isset($record['department_id']) && trim((string) $record['department_id']) !== '') {
                $departmentId = $departmentIdMap[trim((string) $record['department_id'])] ?? null;
            }

            if (! $departmentId) {
                $departmentName = trim((string) ($record['department_name'] ?? $record['department'] ?? ''));
                if ($departmentName !== '') {
                    $departmentId = $departmentNameMap[strtolower($departmentName)] ?? null;
                }
            }

            if (! $departmentId) {
                $skipped++;
                $rowErrors[] = "Line {$lineNumber}: invalid department reference.";
                continue;
            }

            $student = Student::where('student_id', $studentId)->first();

            if ($student) {
                $student->update([
                    'full_name' => $fullName,
                    'department_id' => $departmentId,
                ]);
                $updated++;
            } else {
                Student::create([
                    'student_id' => $studentId,
                    'full_name' => $fullName,
                    'department_id' => $departmentId,
                ]);
                $created++;
            }
        }

        fclose($handle);

        $message = "Bulk upload completed. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}.";

        if (! empty($rowErrors)) {
            $message .= ' Issues: '.implode(' ', array_slice($rowErrors, 0, 5));
        }

        return redirect()->route('students.index')->with('status', $message);
    }
}