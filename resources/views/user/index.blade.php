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
                    
                    <div class="flex justify-between items-center gap-4 mb-6">
                        <div class="flex-1">
                            <h2 class="text-lg font-semibold">List of Users</h2>
                            <p class="text-gray-600">Create, manage and remove users</p>    
                        </div>
                        <div class="flex justify-items-end">
                            <a href="{{ route('users.create') }}" class="p-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Add New</a>
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
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Verified</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="px-6 py-3 text-sm text-gray-900">{{ $loop->iteration }}</td>
                                            <td class="px-6 py-3 text-sm text-gray-900">{{ $user->name }}</td>
                                            <td class="px-6 py-3 text-sm text-gray-900">{{ $user->email }}</td>
                                            <td class="px-6 py-3 text-sm text-gray-900">
                                                @if($user->email_verified_at)
                                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Verified</span>
                                                @else
                                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Pending</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-3 text-sm">
                                                <a href="{{ route('users.show', $user) }}" class="text-blue-500 hover:text-blue-700 mr-3">View</a>
                                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                                                </form>
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