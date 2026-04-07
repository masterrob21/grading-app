<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assessment Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div>
                        <h3 class="text-lg font-semibold">{{ __('Assessment Information') }}</h3>
                        <div class="mt-6 overflow-hidden border border-gray-200 rounded-lg">
                            <table class="w-full border-collapse border text-sm font-medium text-left">
                                <tbody class="bg-white">
                                    <tr>
                                        <th class="px-6 py-4 text-gray-700 bg-gray-100 border-r border-b">{{ __('Course') }}</th>
                                        <td class="px-6 py-4 border-b border-l uppercase">{{ $assessment->course?->course_code ?? '-' }} {{ $assessment->course?->title ? '- ' . $assessment->course->title : '' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="px-6 py-4 text-gray-700 bg-gray-100 border-r border-b">{{ __('Title') }}</th>
                                        <td class="px-6 py-4 border-b border-l capitalize">{{ $assessment->title }}</td>
                                    </tr>
                                    <tr>
                                        <th class="px-6 py-4 text-gray-700 bg-gray-100 border-r border-b">{{ __('Max Score') }}</th>
                                        <td class="px-6 py-4 border-b border-l">{{ number_format((float) $assessment->max_score, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="px-6 py-4 text-gray-700 bg-gray-100 border-r border-b">{{ __('Weight (%)') }}</th>
                                        <td class="px-6 py-4 border-b border-l">{{ number_format((float) $assessment->weight, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="px-6 py-4 text-gray-700 bg-gray-100 border-r border-b">{{ __('Created At') }}</th>
                                        <td class="px-6 py-4 border-b border-l">{{ optional($assessment->created_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="px-6 py-4 text-gray-700 bg-gray-100 border-r border-gray-200">{{ __('Updated At') }}</th>
                                        <td class="px-6 py-4 border-l">{{ optional($assessment->updated_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <a href="{{ route('assessments.edit', $assessment->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">{{ __('Edit Assessment') }}</a>
                            <a href="{{ route('assessments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 transition">{{ __('Back to list') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
