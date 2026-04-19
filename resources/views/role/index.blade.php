<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-gray-800 leading-tight">
			{{ __('Roles') }}
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

			@if($roles->count() > 0)
				<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
					<div class="p-6 text-gray-900">
						<div class="flex justify-between items-center">
							<div>
								<h3 class="text-lg font-semibold text-gray-900">{{ __('Manage Roles') }}</h3>
								<p class="text-sm text-gray-500 mt-1">{{ __('View and manage all roles.') }}</p>
							</div>
							@can('create roles')
							<a href="{{ route('roles.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
								<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
								</svg>
								{{ __('Add Role') }}
							</a>
							@endcan

						</div>
					</div>
				</div>
			@endif

			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
				<div class="p-6 text-gray-900">
					@if($roles->count())
						<div class="overflow-x-auto">
							<table class="w-full text-sm text-left text-gray-600">
								<thead class="text-xs uppercase bg-gray-100 text-gray-700">
									<tr>
										<th class="px-6 py-3">{{ __('#') }}</th>
										<th class="px-6 py-3">{{ __('Role Name') }}</th>
										<th class="px-6 py-3">{{ __('Created At') }}</th>
										<th class="px-6 py-3">{{ __('Actions') }}</th>
									</tr>
								</thead>
								<tbody>
									@foreach($roles as $index => $role)
										<tr class="bg-white border-b hover:bg-gray-50 transition">
											<td class="px-6 py-2 font-medium text-gray-900">
												{{ $index + 1 }}
											</td>
											<td class="px-6 py-2 capitalize">
												{{ $role->name }}
											</td>
											<td class="px-6 py-2">
												{{ $role->created_at->format('M d, Y') }}
											</td>
											<td class="px-6 py-2 space-x-2 flex">
												@can('edit roles')
												<a href="{{ route('roles.edit', $role->id) }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200 transition font-medium text-xs">
													<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
														<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
													</svg>
													{{ __('Edit') }}
												</a>
												@endcan

												@can('delete roles')
												<form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ __('Are you sure you want to delete this role?') }}');">
													@csrf
													@method('DELETE')
													<button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition font-medium text-xs">
														<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
															<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
														</svg>
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
							<svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
							</svg>
							<h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No roles') }}</h3>
							<p class="mt-1 text-sm text-gray-500">{{ __('Get started by creating a new role.') }}</p>
							<div class="mt-6">
								@can('create roles')
								<a href="{{ route('roles.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
									<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
									</svg>
									{{ __('Create Role') }}
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
			$('#success-message, #error-message').delay(5000).fadeOut('slow', function() {
				$(this).remove();
			});
		});
	</script>
</x-app-layout>
