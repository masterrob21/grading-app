<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl mx-auto">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">User Information</h3>
                        <p class="text-sm text-gray-600">Details for {{ $user->name }}</p>
                    </div>

                    <table class="w-full border-collapse border border-gray-300">
                        <tbody>
                            <tr class="border-b border-gray-300">
                                <td class="px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50">Name</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $user->name }}</td>
                            </tr>
                            <tr class="border-b border-gray-300">
                                <td class="px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50">Email</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $user->email }}</td>
                            </tr>
                            <tr class="border-b border-gray-300">
                                <td class="px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50">Email Verified</td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    @if($user->email_verified_at)
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Verified</span>
                                    @else
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-b border-gray-300">
                                <td class="px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50">Roles</td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    @if($user->roles->isNotEmpty())
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($user->roles as $role)
                                                <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs">{{ $role->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">No roles assigned</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-b border-gray-300">
                                <td class="px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50">Created At</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $user->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50">Updated At</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $user->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex items-center justify-between mt-8">
                        <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to Users</a>
                        
                        @can('edit users')
                        <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Edit User
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>