<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div>
                    @if(session('success'))
                        <div id="session-message" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                    <div id="error-message" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm font-medium text-red-800">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="flex justify-between items-center gap-4 mb-6">
                        <div class="flex-1">
                            <h2 class="text-lg font-semibold">List of Users</h2>
                            <p class="text-gray-600">Create, manage and remove users</p>    
                        </div>
                        <div class="flex justify-items-end">
                            @can('create users')
                            <a href="{{ route('users.create') }}" class="p-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Add New</a>
                            @endcan
                        </div>    
                    </div>
                    
                    @if($users->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-100 border-b">
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">#</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Roles</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Verified</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="px-6 py-2 text-sm text-gray-900">{{ $loop->iteration }}</td>
                                            <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">{{ $user->name }}</td>
                                            <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">{{ $user->email }}</td>
                                            <td class="px-6 py-2 text-sm text-gray-900">
                                                @if($user->roles->isNotEmpty())
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach($user->roles as $role)
                                                            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs">{{ $role->name }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 text-xs">No roles assigned</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">
                                                @if($user->email_verified_at)
                                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Verified</span>
                                                @else
                                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Pending</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-2 text-sm whitespace-nowrap">
                                                <a href="{{ route('users.show', $user) }}" class="bg-blue-100 text-blue-700 hover:bg-blue-200 transition px-3 py-1.5 rounded-md mr-3">View</a>
                                                @can('delete users')
                                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="bg-red-100 text-red-700 hover:bg-red-200 transition px-3 py-1.5 rounded-md">Delete</button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">No users found.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script type="module">
        $(document).ready(function() {
            // Auto-hide session message after 5 seconds
            setTimeout(function() {
                $('#session-message').fadeOut('slow');
            }, 5000);
        });
    </script>
</x-app-layout>