<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $totalUsers = \App\Models\User::count();
        $totalStudents = \App\Models\Student::count();
        $totalDepartments = \App\Models\Department::count();
        $totalCourses = \App\Models\Course::count();
        $activeAcademicYearModel = \App\Models\AcademicYear::where('is_current', true)->first();
        $activeAcademicYear = $activeAcademicYearModel?->year;

        $currentYearEnrollments = 0;
        $enrollmentByCourse = collect();

        if ($activeAcademicYearModel) {
            $currentYearEnrollments = \App\Models\Enrollment::where('academic_year_id', $activeAcademicYearModel->id)->count();
            $enrollmentByCourse = \App\Models\Enrollment::selectRaw('course_id, COUNT(*) as total')
                ->where('academic_year_id', $activeAcademicYearModel->id)
                ->groupBy('course_id')
                ->orderByDesc('total')
                ->with('course:id,course_code,title')
                ->get();
        }

        $markScoreSummary = (object) ['total_marks' => 0, 'total_score' => 0];
        $courseMarkTotals = collect();
        $studentCourseTotals = collect();

        if ($activeAcademicYearModel) {
            $markQuery = \App\Models\Mark::query()
                ->whereHas('enrollment', function ($query) use ($activeAcademicYearModel) {
                    $query->where('academic_year_id', $activeAcademicYearModel->id);
                });

            $markScoreSummary = (clone $markQuery)
                ->selectRaw('COUNT(*) as total_marks, SUM(score) as total_score')
                ->first();

            $courseMarkTotals = \App\Models\Mark::query()
                ->join('enrollments', 'marks.enrollment_id', '=', 'enrollments.id')
                ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                ->where('enrollments.academic_year_id', $activeAcademicYearModel->id)
                ->selectRaw("courses.id, courses.course_code, courses.title, SUM(marks.score) as total_score, AVG(marks.score) as average_score, MIN(marks.score) as minimum_score, MAX(marks.score) as maximum_score")
                ->groupBy('courses.id', 'courses.course_code', 'courses.title')
                ->orderByDesc('total_score')
                ->get();

            $studentCourseTotals = \App\Models\Mark::query()
                ->join('enrollments', 'marks.enrollment_id', '=', 'enrollments.id')
                ->join('courses', 'enrollments.course_id', '=', 'courses.id')
                ->where('enrollments.academic_year_id', $activeAcademicYearModel->id)
                ->selectRaw("courses.id, courses.course_code, courses.title, enrollments.student_id, SUM(marks.score) as student_total_score")
                ->groupBy('courses.id', 'courses.course_code', 'courses.title', 'enrollments.student_id')
                ->get();
        }

        $courseStudentAggregateStats = $studentCourseTotals
            ->groupBy('id')
            ->map(function ($rows) {
                $firstRow = $rows->first();

                return [
                    'label' => trim(($firstRow->course_code ?? '') ),
                    'average_total' => round((float) $rows->avg('student_total_score'), 2),
                    'minimum_total' => round((float) $rows->min('student_total_score'), 2),
                    'maximum_total' => round((float) $rows->max('student_total_score'), 2),
                    'sort_total' => (float) $rows->sum('student_total_score'),
                ];
            })
            ->sortByDesc('sort_total')
            ->values();

        $markChartLabels = $courseStudentAggregateStats->pluck('label')->all();
        $averageMarkChartData = $courseStudentAggregateStats->pluck('average_total')->all();
        $minimumMarkChartData = $courseStudentAggregateStats->pluck('minimum_total')->all();
        $maximumMarkChartData = $courseStudentAggregateStats->pluck('maximum_total')->all();

        $averageCourseTotal = $courseStudentAggregateStats->avg('average_total') ?? 0;
        $minimumCourseTotal = $courseStudentAggregateStats->min('minimum_total') ?? 0;
        $maximumCourseTotal = $courseStudentAggregateStats->max('maximum_total') ?? 0;

        $roles = auth()->user()?->getRoleNames() ?? collect();
        $roleDisplay = $roles->isNotEmpty() ? $roles->implode(', ') : 'No role assigned';
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg transition-colors duration-200 hover:bg-gray-50">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900">Welcome back, {{ auth()->user()->name }}.</h3>
                    <p class="mt-1 text-sm text-gray-600">Roles: <span class="font-medium text-red-800">{{ $roleDisplay }}</span></p>
                    <p class="mt-3 text-sm text-gray-500">Use the cards and quick links below to manage the grading system efficiently.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 transition-colors duration-200 hover:bg-gray-50">
                    <p class="text-sm text-gray-500">Total Users</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $totalUsers }}</p>
                    <a href="{{ route('users.index') }}" class="mt-3 inline-flex text-sm font-medium text-blue-600 hover:text-blue-700">Manage users</a>
                </div>

                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 transition-colors duration-200 hover:bg-gray-50">
                    <p class="text-sm text-gray-500">Total Students</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $totalStudents }}</p>
                    <a href="{{ route('students.index') }}" class="mt-3 inline-flex text-sm font-medium text-blue-600 hover:text-blue-700">Manage students</a>
                </div>

                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 transition-colors duration-200 hover:bg-gray-50">
                    <p class="text-sm text-gray-500">Total Departments</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $totalDepartments }}</p>
                    <a href="{{ route('departments.index') }}" class="mt-3 inline-flex text-sm font-medium text-blue-600 hover:text-blue-700">Manage departments</a>
                </div>

                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 transition-colors duration-200 hover:bg-gray-50">
                    <p class="text-sm text-gray-500">Total Courses</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $totalCourses }}</p>
                    <a href="{{ route('courses.index') }}" class="mt-3 inline-flex text-sm font-medium text-blue-600 hover:text-blue-700">Manage courses</a>
                </div>

                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 transition-colors duration-200 hover:bg-gray-50">
                    <p class="text-sm text-gray-500">Active Academic Year</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $activeAcademicYear ?? 'Not set' }}</p>
                    <a href="{{ route('academic_years.index') }}" class="mt-3 inline-flex text-sm font-medium text-blue-600 hover:text-blue-700">Manage academic years</a>
                </div>

                <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 transition-colors duration-200 hover:bg-gray-50">
                    <p class="text-sm text-gray-500">Enrollments (Active Year)</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $currentYearEnrollments }}</p>
                    <a href="{{ route('enrollments.index') }}" class="mt-3 inline-flex text-sm font-medium text-blue-600 hover:text-blue-700">Manage enrollments</a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 transition-colors duration-200 hover:bg-gray-50">
                <h4 class="text-lg font-semibold text-gray-900">Enrollments by Course (Active Year)</h4>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $activeAcademicYear ? 'Academic Year: ' . $activeAcademicYear : 'No active academic year set.' }}
                </p>

                @if($activeAcademicYear && $enrollmentByCourse->count())
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600">
                            <thead class="text-xs uppercase bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="px-4 py-2">Course</th>
                                    <th class="px-4 py-2">Code</th>
                                    <th class="px-4 py-2">Enrollments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrollmentByCourse as $row)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">{{ $row->course?->title ?? 'N/A' }}</td>
                                        <td class="px-4 py-2">{{ $row->course?->course_code ?? 'N/A' }}</td>
                                        <td class="px-4 py-2 font-semibold text-gray-900">{{ $row->total }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-500">No enrollment records found for the active academic year.</p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 transition-colors duration-200 hover:bg-gray-50">
                <h4 class="text-lg font-semibold text-gray-900">Mark Score Analysis by Course</h4>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $activeAcademicYear ? 'Average, minimum, and maximum student total scores by course for: ' . $activeAcademicYear : 'No active academic year set.' }}
                </p>

                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="rounded-md bg-gray-50 p-3 border border-gray-100 transition-colors duration-200 hover:bg-gray-100">
                        <p class="text-xs text-gray-500">Total Marks</p>
                        <p class="text-xl font-semibold text-gray-900">{{ (int) ($markScoreSummary->total_marks ?? 0) }}</p>
                    </div>
                    <div class="rounded-md bg-gray-50 p-3 border border-gray-100 transition-colors duration-200 hover:bg-gray-100">
                        <p class="text-xs text-gray-500">Average Course Total</p>
                        <p class="text-xl font-semibold text-gray-900">{{ number_format((float) $averageCourseTotal, 2) }}</p>
                    </div>
                    <div class="rounded-md bg-gray-50 p-3 border border-gray-100 transition-colors duration-200 hover:bg-gray-100">
                        <p class="text-xs text-gray-500">Lowest Course Total</p>
                        <p class="text-xl font-semibold text-gray-900">{{ number_format((float) $minimumCourseTotal, 2) }}</p>
                    </div>
                    <div class="rounded-md bg-gray-50 p-3 border border-gray-100 transition-colors duration-200 hover:bg-gray-100">
                        <p class="text-xs text-gray-500">Highest Course Total</p>
                        <p class="text-xl font-semibold text-gray-900">{{ number_format((float) $maximumCourseTotal, 2) }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <canvas id="marksDistributionChart" height="110"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 transition-colors duration-200 hover:bg-gray-50">
                    <h4 class="text-lg font-semibold text-gray-900">Quick Actions</h4>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <a href="{{ route('students.create') }}" class="px-4 py-3 rounded-md border border-gray-200 hover:bg-gray-50 text-sm font-medium text-gray-700">Add Student</a>
                        <a href="{{ route('users.create') }}" class="px-4 py-3 rounded-md border border-gray-200 hover:bg-gray-50 text-sm font-medium text-gray-700">Add User</a>
                        <a href="{{ route('courses.create') }}" class="px-4 py-3 rounded-md border border-gray-200 hover:bg-gray-50 text-sm font-medium text-gray-700">Add Course</a>
                        <a href="{{ route('marks.create') }}" class="px-4 py-3 rounded-md border border-gray-200 hover:bg-gray-50 text-sm font-medium text-gray-700">Record Mark</a>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 transition-colors duration-200 hover:bg-gray-50">
                    <h4 class="text-lg font-semibold text-gray-900">System Navigation</h4>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li><a href="{{ route('roles.index') }}" class="text-blue-600 hover:text-blue-700 font-medium">Roles Management</a></li>
                        <li><a href="{{ route('roles.permissions.index') }}" class="text-blue-600 hover:text-blue-700 font-medium">Role Permissions</a></li>
                        <li><a href="{{ route('course_users.index') }}" class="text-blue-600 hover:text-blue-700 font-medium">Course Users</a></li>
                        <li><a href="{{ route('assessments.index') }}" class="text-blue-600 hover:text-blue-700 font-medium">Assessments</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const markChartLabels = @json($markChartLabels);
        const averageMarkChartData = @json($averageMarkChartData);
        const minimumMarkChartData = @json($minimumMarkChartData);
        const maximumMarkChartData = @json($maximumMarkChartData);
        const markCanvas = document.getElementById('marksDistributionChart');

        if (markCanvas) {
            new Chart(markCanvas, {
                type: 'bar',
                data: {
                    labels: markChartLabels,
                    datasets: [
                        {
                            label: 'Average',
                            data: averageMarkChartData,
                            backgroundColor: '#3b82f6',
                            borderRadius: 6,
                        },
                        {
                            label: 'Minimum',
                            data: minimumMarkChartData,
                            backgroundColor: '#f59e0b',
                            borderRadius: 6,
                        },
                        {
                            label: 'Maximum',
                            data: maximumMarkChartData,
                            backgroundColor: '#22c55e',
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    </script>
</x-app-layout>
