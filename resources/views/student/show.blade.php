<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <table class="w-full text-sm text-left text-gray-600 border-collapse border border-gray-300">
                        <caption class="text-lg font-semibold text-gray-900 mb-4 text-left">{{ __('Student Information') }}</caption>
                        <tbody>
                            <tr class="border-b border-gray-300">
                                <td class="px-6 py-4 font-medium text-gray-900 w-1/3 border-r border-gray-300 bg-blue-50">{{ __('Student ID') }}</td>
                                <td class="px-6 py-4">{{ $student->student_id }}</td>
                            </tr>
                            <tr class="border-b border-gray-300">
                                <td class="px-6 py-4 font-medium text-gray-900 border-r border-gray-300 bg-blue-50">{{ __('Full Name') }}</td>
                                <td class="px-6 py-4 capitalize">{{ $student->full_name }}</td>
                            </tr>
                            <tr class="border-b border-gray-300">
                                <td class="px-6 py-4 font-medium text-gray-900 border-r border-gray-300 bg-blue-50">{{ __('Department') }}</td>
                                <td class="px-6 py-4 capitalize">{{ $student->department?->department_name ?? '-' }}</td>
                            </tr>
                            <tr class="border-b border-gray-300">
                                <td class="px-6 py-4 font-medium text-gray-900 border-r border-gray-300 bg-blue-50">{{ __('Created At') }}</td>
                                <td class="px-6 py-4">{{ $student->created_at->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-900 border-r border-gray-300 bg-blue-50">{{ __('Last Updated') }}</td>
                                <td class="px-6 py-4">{{ $student->updated_at->format('M d, Y') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                        <a href="{{ route('students.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 transition">{{ __('Back to list') }}</a>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('students.edit', $student->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-700 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-yellow-200 transition">{{ __('Edit') }}</a>
                            <form action="{{ route('students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this student?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-100 text-red-700 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-red-200 transition">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
