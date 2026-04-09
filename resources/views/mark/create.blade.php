<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Enter Mark') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="max-w-xl mx-auto">
                        <form action="{{ route('marks.store') }}" method="POST" class="space-y-6">
                            @csrf

                            <div>
                                <label for="course_id" class="block text-sm font-medium text-gray-700">{{ __('Course') }}</label>
                                <select name="course_id" id="course_id" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">{{ __('Select a course') }}</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->course_code }} - {{ $course->title }}</option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="assessment_id" class="block text-sm font-medium text-gray-700">{{ __('Assessment') }}</label>
                                <select name="assessment_id" id="assessment_id" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">{{ __('Select an assessment') }}</option>
                                    @foreach($assessments as $assessment)
                                        <option value="{{ $assessment->id }}" data-course-id="{{ $assessment->course_id }}" {{ old('assessment_id') == $assessment->id ? 'selected' : '' }}>
                                            {{ $assessment->title }} ({{ $assessment->course?->course_code ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('assessment_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="student_id" class="block text-sm font-medium text-gray-700">{{ __('Student ID') }}</label>
                                <input type="text" name="student_id" id="student_id" value="{{ old('student_id') }}" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500" required>
                                <div id="student_name" class="mt-2 text-sm text-gray-600"></div>
                                @error('student_id')
                                    <p id="student_id_error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="score" class="block text-sm font-medium text-gray-700">{{ __('Score') }}</label>
                                <input type="number" step="0.01" min="0" max="999.99" name="score" id="score" value="{{ old('score') }}" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:border-blue-500 focus:ring-blue-500" required>
                                @error('score')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <label class="flex items-center gap-3">
                                <input type="checkbox" name="is_locked" value="1" {{ old('is_locked') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">{{ __('Lock this mark after saving') }}</span>
                            </label>

                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('marks.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 transition">{{ __('Cancel') }}</a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">{{ __('Save Mark') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const courseSelect = document.getElementById('course_id');
            const assessmentSelect = document.getElementById('assessment_id');
            const assessmentOptions = Array.from(assessmentSelect.querySelectorAll('option[data-course-id]'));
            const studentIdInput = document.getElementById('student_id');
            const studentNameDiv = document.getElementById('student_name');
            const studentErrorDiv = document.getElementById('student_id_error');
            let timeout;

            function filterAssessments() {
                const selectedCourseId = courseSelect.value;
                const currentValue = assessmentSelect.value;

                assessmentOptions.forEach(function (option) {
                    option.hidden = selectedCourseId !== '' && option.dataset.courseId !== selectedCourseId;
                });

                const selectedOption = assessmentSelect.querySelector('option[value="' + currentValue + '"]');
                if (selectedOption && selectedOption.hidden) {
                    assessmentSelect.value = '';
                }
            }

            courseSelect.addEventListener('change', filterAssessments);
            filterAssessments();

            studentIdInput.addEventListener('input', function () {
                clearTimeout(timeout);
                const studentId = this.value.trim();

                if (studentId.length === 0) {
                    studentNameDiv.textContent = '';
                    if (studentErrorDiv) {
                        studentErrorDiv.textContent = '';
                    }
                    return;
                }

                timeout = setTimeout(function () {
                    fetch('{{ route('students.lookup', ['student_id' => '__STUDENT_ID__']) }}'.replace('__STUDENT_ID__', encodeURIComponent(studentId)))
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Student not found');
                            }

                            return response.json();
                        })
                        .then(function (data) {
                            studentNameDiv.textContent = data.name ? 'Student: ' + data.name : 'Student not found';
                            studentNameDiv.className = data.name ? 'mt-2 text-sm text-green-600' : 'mt-2 text-sm text-red-600';
                        })
                        .catch(function () {
                            studentNameDiv.textContent = 'Student not found';
                            studentNameDiv.className = 'mt-2 text-sm text-red-600';
                        });
                }, 400);
            });
        });
    </script>
</x-app-layout>