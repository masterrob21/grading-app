<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Settings') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-gray-900">{{ __('System Settings') }}</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('Manage core records used across the grading system. Use the links below to open each setup page.') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="border border-gray-200 rounded-lg p-2">
                            <h4 class="text-lg font-semibold text-gray-900">{{ __('Users') }}</h4>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Create and manage user accounts for lecturers and administrators.') }}</p>
                            <a href="{{ route('users.index') }}" class="mt-2 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                {{ __('Go to Users') }}
                            </a>
                        </div>

                        <div class="border border-gray-200 rounded-lg p-2">
                            <h4 class="text-lg font-semibold text-gray-900">{{ __('Roles') }}</h4>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Create and manage roles for users.') }}</p>
                            <a href="{{ route('roles.index') }}" class="mt-2 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                {{ __('Go to Roles') }}
                            </a>
                        </div>

                        <div class="border border-gray-200 rounded-lg p-2">
                            <h4 class="text-lg font-semibold text-gray-900">{{ __('Role Permissions') }}</h4>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Create and manage permission for users.') }}</p>
                            <a href="{{ route('roles.permissions.index') }}" class="mt-2 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                {{ __('Go to Permissions') }}
                            </a>
                        </div>

                        <div class="border border-gray-200 rounded-lg p-2">
                            <h4 class="text-lg font-semibold text-gray-900">{{ __('Courses') }}</h4>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Manage courses and their details.') }}</p>
                            <a href="{{ route('courses.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                {{ __('Go to Courses') }}
                            </a>
                        </div>

                        <div class="border border-gray-200 rounded-lg p-2">
                            <h4 class="text-lg font-semibold text-gray-900">{{ __('Course Users') }}</h4>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Assign users to courses and maintain course access records.') }}</p>
                            <a href="{{ route('course_users.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                {{ __('Go to Course Users') }}
                            </a>
                        </div>

                        <div class="border border-gray-200 rounded-lg p-2">
                            <h4 class="text-lg font-semibold text-gray-900">{{ __('Departments') }}</h4>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Manage the list of academic departments in your institution.') }}</p>
                            <a href="{{ route('departments.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                {{ __('Go to Departments') }}
                            </a>
                        </div>

                        <div class="border border-gray-200 rounded-lg p-2">
                            <h4 class="text-lg font-semibold text-gray-900">{{ __('Academic Years') }}</h4>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Set up and update available academic year periods.') }}</p>
                            <a href="{{ route('academic_years.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                {{ __('Go to Academic Years') }}
                            </a>
                        </div>

                        <div class="border border-gray-200 rounded-lg p-2">
                            <h4 class="text-lg font-semibold text-gray-900">{{ __('Assessments') }}</h4>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Manage assessments and their details.') }}</p>
                            <a href="{{ route('assessments.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                {{ __('Go to Assessments') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
