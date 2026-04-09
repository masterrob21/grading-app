<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Academic Years') }}</h2>
        </div>
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
            
            @if($academicYears->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Manage Academic Year') }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ __('View, show, and delete records.') }}</p>
                        </div>
                        <a href="{{ route('academic_years.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('add Academic Year') }}
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($academicYears->count())
                        <div class="overflow-x-auto">
                            <table class="w-full class="w-full text-sm text-left text-gray-600">
                                <thead class="text-xs uppercase bg-gray-100 text-gray-700"
                                    <tr>
                                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('#') }}</th>
                                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Year') }}</th>
                                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($academicYears as $index => $year)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-900">{{ $year->year }}</td>
                                            <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-600">{{ $year->is_current ? __('Current') : __('Inactive') }}</td>
                                            <td class="px-2 py-2 text-sm">
                                                <a href="{{ route('academic_years.edit', $year->id) }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200 transition font-medium text-xs mb-1 md:mb-0">{{ __('Edit') }}</a>
                                                <form action="{{ route('academic_years.destroy', $year->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this academic year?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition font-medium text-xs">{{ __('Delete') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('No academic years found') }}</h3>
                            <p class="mt-2 text-sm text-gray-500">{{ __('Create a new academic year to get started.') }}</p>
                            <a href="{{ route('academic_years.create') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">{{ __('Add Academic Year') }}</a>
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
