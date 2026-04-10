<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-gray-800 leading-tight">
			{{ __('Role Permissions') }}
		</h2>
	</x-slot>

	<div class="py-12">
		<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
			@if(session('success'))
				<div id="success-message" class="p-4 bg-green-50 border border-green-200 rounded-lg">
					<p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
				</div>
			@endif

			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
				<div class="p-6 text-gray-900">
					<form method="GET" action="{{ route('roles.permissions.index') }}" class="space-y-4">
						<div>
							<label for="role" class="block text-sm font-medium text-gray-700">Select Role</label>
							<select
								name="role"
								id="role"
								class="mt-1 block w-full max-w-md rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm"
								onchange="this.form.submit()"
							>
								<option value="">-- Choose a role --</option>
								@foreach($roles as $role)
									<option value="{{ $role->id }}" {{ (string) request('role') === (string) $role->id ? 'selected' : '' }}>
										{{ $role->name }}
									</option>
								@endforeach
							</select>
						</div>
					</form>
				</div>
			</div>

			@if($selectedRole)
				<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
					<div class="p-6 text-gray-900">
						<div class="mb-6">
							<h3 class="text-lg font-semibold text-gray-900">{{ __('Edit Permissions for:') }} {{ $selectedRole->name }}</h3>
							<p class="text-sm text-gray-500 mt-1">{{ __('Check or uncheck permissions, then save changes.') }}</p>
						</div>

						@php($checkedPermissions = old('permissions', $selectedPermissionNames))

						<form method="POST" action="{{ route('roles.permissions.update', $selectedRole->id) }}" class="space-y-6">
							@csrf
							@method('PUT')

							<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
								@forelse($permissionsByModule as $module => $permissions)
									<fieldset class="border border-gray-200 rounded-lg p-4">
										<legend class="px-1 text-sm font-semibold text-gray-700 capitalize">
											{{ str_replace('_', ' ', $module) }}
										</legend>

										<div class="space-y-2 mt-2">
											@foreach($permissions as $permission)
												<label class="flex items-center gap-3">
													<input
														type="checkbox"
														name="permissions[]"
														value="{{ $permission->name }}"
														class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
														{{ in_array($permission->name, $checkedPermissions, true) ? 'checked' : '' }}
													>
													<span class="text-sm text-gray-700">{{ $permission->name }}</span>
												</label>
											@endforeach
										</div>
									</fieldset>
								@empty
									<p class="text-sm text-gray-500">No permissions available.</p>
								@endforelse
							</div>

							@error('permissions')
								<p class="text-sm text-red-600">{{ $message }}</p>
							@enderror
							@error('permissions.*')
								<p class="text-sm text-red-600">{{ $message }}</p>
							@enderror

							<div class="flex items-center justify-end">
								<button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
									{{ __('Save Permissions') }}
								</button>
							</div>
						</form>
					</div>
				</div>
			@else
				<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
					<div class="p-6 text-gray-500 text-sm">
						Select a role to manage its permissions.
					</div>
				</div>
			@endif
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
