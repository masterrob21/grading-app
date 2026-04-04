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
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Student ID') }}</p>
                            <p class="mt-2 text-lg font-medium text-gray-900">{{ $student->student_id }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Full Name') }}</p>
                            <p class="mt-2 text-lg font-medium text-gray-900">{{ $student->full_name }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg sm:col-span-2">
                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Department') }}</p>
                            <p class="mt-2 text-lg font-medium text-gray-900">{{ $student->department?->department_name ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Created At') }}</p>
                            <p class="mt-2 text-lg font-medium text-gray-900">{{ $student->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Last Updated') }}</p>
                            <p class="mt-2 text-lg font-medium text-gray-900">{{ $student->updated_at->format('M d, Y') }}</p>
                        </div>
                    </div>

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
