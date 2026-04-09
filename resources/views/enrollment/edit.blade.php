<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Enrollment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="max-w-xl mx-auto">
                        <form action="{{ route('enrollments.update', $enrollment->id) }}" method="POST" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <div>
                                <label for="student_id" class="block text-sm font-medium text-gray-700">{{ __('Student ID') }}</label>
                                <input type="text" name="student_id" id="student_id" value="{{ old('student_id', $enrollment->student_id) }}" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500" required>
                                <div id="student_name" class="mt-2 text-sm text-gray-600">{{ $enrollment->student ? 'Student: ' . $enrollment->student->full_name : '' }}</div>
                                @error('student_id')
                                    <p id="error_message" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="course_id" class="block text-sm font-medium text-gray-700">{{ __('Course') }}</label>
                                <select name="course_id" id="course_id" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">{{ __('Select a course') }}</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id', $enrollment->course_id) == $course->id ? 'selected' : '' }}>{{ $course->course_code }} - {{ $course->title }}</option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="academic_year_id" class="block text-sm font-medium text-gray-700">{{ __('Academic Year') }}</label>
                                <select name="academic_year_id" id="academic_year_id" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">{{ __('Select an academic year') }}</option>
                                    @foreach($academicYears as $academicYear)
                                        <option value="{{ $academicYear->id }}" {{ old('academic_year_id', $enrollment->academic_year_id) == $academicYear->id ? 'selected' : '' }}>{{ $academicYear->year }}</option>
                                    @endforeach
                                </select>
                                @error('academic_year_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('enrollments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 transition">{{ __('Cancel') }}</a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">{{ __('Update Enrollment') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
 document.addEventListener('DOMContentLoaded', function() {
    const studentIdInput = document.getElementById('student_id');
    const studentNameDiv = document.getElementById('student_name');
    const errorMessageDiv = document.getElementById('error_message');
    const lookupRouteTemplate = "{{ route('students.lookup', ['student_id' => '__STUDENT_ID__']) }}";

    let timeout;

    studentIdInput.addEventListener('input', function() {
        clearTimeout(timeout);
        const studentId = this.value.trim();

        if (studentId.length === 0) {
            studentNameDiv.textContent = '';
            if (errorMessageDiv) {
                errorMessageDiv.textContent = '';
            }
            return;
        }

        timeout = setTimeout(() => {
            const lookupUrl = lookupRouteTemplate.replace('__STUDENT_ID__', encodeURIComponent(studentId));

            fetch(lookupUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.name) {
                        studentNameDiv.textContent = `Student: ${data.name}`;
                        if (errorMessageDiv) {
                            errorMessageDiv.textContent = '';
                        }
                        studentNameDiv.className = 'mt-2 text-sm text-green-600';
                    } else {
                        studentNameDiv.textContent = 'Student not found';
                        studentNameDiv.className = 'mt-2 text-sm text-red-600';
                    }
                })
                .catch(error => {
                    studentNameDiv.textContent = 'Error checking student';
                    studentNameDiv.className = 'mt-2 text-sm text-red-600';
                });
        }, 500); // Debounce for 500ms
    });
});
</script>
