<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Enrollments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div id="success-message" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm font-medium text-green-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            @endif

            @if($enrollments->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Manage Enrollments') }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ __('View, show, and delete enrollment records.') }}</p>
                        </div>
                        <a href="{{ route('enrollments.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Add Enrollment') }}
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($enrollments->count())
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-600">
                                <thead class="text-xs uppercase bg-gray-100 text-gray-700">
                                    <tr>
                                        <th class="px-6 py-3">{{ __('#') }}</th>
                                        <th class="px-6 py-3">{{ __('Student ID') }}</th>
                                        <th class="px-6 py-3">{{ __('Student Name') }}</th>
                                        <th class="px-6 py-3">{{ __('Course') }}</th>
                                        <th class="px-6 py-3">{{ __('Academic Year') }}</th>
                                        <th class="px-6 py-3">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrollments as $index => $enrollment)
                                        <tr class="bg-white border-b hover:bg-gray-50 transition">
                                            <td class="px-6 py-2 font-medium text-gray-900">{{ $index + 1 }}</td>
                                            <td class="px-6 py-2">{{ $enrollment->student?->student_id ?? '-' }}</td>
                                            <td class="px-6 py-2 capitalize">{{ $enrollment->student?->full_name ?? '-' }}</td>
                                            <td class="px-6 py-2 uppercase">{{ $enrollment->course?->course_code ?? '-' }} {{ $enrollment->course?->title ? '- ' . $enrollment->course->title : '' }}</td>
                                            <td class="px-6 py-2">{{ $enrollment->academicYear?->year ?? '-' }}</td>
                                            <td class="px-6 py-2">
                                                <a href="{{ route('enrollments.show', $enrollment->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition font-medium text-xs mb-1 md:mb-0">
                                                    {{ __('Show') }}
                                                </a>
                                                <form action="{{ route('enrollments.destroy', $enrollment->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this enrollment?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition font-medium text-xs">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('No enrollments found') }}</h3>
                            <p class="mt-2 text-sm text-gray-500">{{ __('Create a new enrollment to get started.') }}</p>
                            <a href="{{ route('enrollments.create') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Add Enrollment') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        document.addEventListener('DOMContentLoaded', function () {
            const successMessage = document.getElementById('success-message');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.remove();
                }, 5000);
            }
        });
    </script>
</x-app-layout>
