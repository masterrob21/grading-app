<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Students') }}
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

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-800 list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @if($students->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Manage Students') }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ __('View, show, and delete student records.') }}</p>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full md:w-auto">
                            @can('upload bulk students')
                            <a href="{{ route('students.sample_csv') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition ease-in-out duration-150 whitespace-nowrap">
                                {{ __('Download Sample CSV') }}
                            </a>
                            <form action="{{ route('students.bulk_upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-center gap-2 w-full">
                                @csrf
                                <input type="file" name="students_file" accept=".csv,text/csv" required class="block w-full text-sm text-gray-700 border border-gray-300 rounded-md cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 whitespace-nowrap">
                                    {{ __('Upload Bulk List') }}
                                </button>
                            </form>
                            @endcan
                            @can('create students')
                            <a href="{{ route('students.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 whitespace-nowrap">
                                {{ __('Add Student') }}
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($students->count())
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-600">
                                <thead class="text-xs uppercase bg-gray-100 text-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 whitespace-nowrap">{{ __('#') }}</th>
                                        <th class="px-6 py-3 whitespace-nowrap">{{ __('Student ID') }}</th>
                                        <th class="px-6 py-3 whitespace-nowrap">{{ __('Full Name') }}</th>
                                        <th class="px-6 py-3 whitespace-nowrap">{{ __('Department') }}</th>
                                        <th class="px-6 py-3 whitespace-nowrap">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $student)
                                        <tr class="bg-white border-b hover:bg-gray-50 transition">
                                            <td class="px-6 py-2 font-medium text-gray-900">{{ $index + 1 }}</td>
                                            <td class="px-6 py-2 whitespace-nowrap">{{ $student->student_id }}</td>
                                            <td class="px-6 py-2 capitalize whitespace-nowrap">{{ $student->full_name }}</td>
                                            <td class="px-6 py-2 capitalize whitespace-nowrap">{{ $student->department?->department_name ?? '-' }}</td>
                                            <td class="px-6 py-2 whitespace-nowrap">
                                                <a href="{{ route('students.show', $student->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition font-medium text-xs mb-1 md:mb-0">
                                                    {{ __('Show') }}
                                                </a>
                                                @can('delete students')
                                                <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this student?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition font-medium text-xs">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('No students found') }}</h3>
                            <p class="mt-2 text-sm text-gray-500">{{ __('Create a new student or upload a CSV list to get started.') }}</p>
                            <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                                @can('upload bulk students')
                                <a href="{{ route('students.sample_csv') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition ease-in-out duration-150 whitespace-nowrap">
                                    {{ __('Download Sample CSV') }}
                                </a>
                                <form action="{{ route('students.bulk_upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-2">
                                    @csrf
                                    <input type="file" name="students_file" accept=".csv,text/csv" required class="block w-full text-sm text-gray-700 border border-gray-300 rounded-md cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 whitespace-nowrap">
                                        {{ __('Upload Bulk List') }}
                                    </button>
                                </form>
                                @endcan
                                @can('create students')
                                <a href="{{ route('students.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Add Student') }}
                                </a>
                                @endcan
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        $(document).ready(function() {
            $('#success-message').delay(5000).fadeOut('slow', function() {
                $(this).remove();
            });
        });
    </script>
</x-app-layout>
