<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-gray-800 leading-tight">
			{{ __('My Marks') }}
		</h2>
	</x-slot>

	<div class="py-12">
		<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
			@if(session('success'))
				<div id="success-message" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm font-medium">
					{{ session('success') }}
				</div>
			@endif

			@if(session('warning'))
				<div id="warning-message" class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm font-medium">
					{{ session('warning') }}
				</div>
			@endif

			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
				<div class="p-6 text-gray-900 flex justify-between items-center gap-4">
					<div>
						<h3 class="text-lg font-semibold text-gray-900">{{ __('Entered Marks') }}</h3>
						<p class="text-sm text-gray-500 mt-1">{{ __('Only marks entered by your account are shown here.') }}</p>
					</div>
					<a href="{{ route('marks.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
						{{ __('Enter Mark') }}
					</a>
				</div>
			</div>

			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
				<div class="p-6 text-gray-900">
					@if($marks->count())
						<div class="overflow-x-auto">
							<table class="w-full text-sm text-left text-gray-600">
								<thead class="text-xs uppercase bg-gray-100 text-gray-700">
									<tr>
										<th class="px-6 py-3">{{ __('#') }}</th>
										<th class="px-6 py-3">{{ __('Student ID') }}</th>
										<th class="px-6 py-3">{{ __('Student Name') }}</th>
										<th class="px-6 py-3">{{ __('Course') }}</th>
										<th class="px-6 py-3">{{ __('Assessment') }}</th>
										<th class="px-6 py-3">{{ __('Score') }}</th>
										<th class="px-6 py-3">{{ __('Status') }}</th>
										<th class="px-6 py-3">{{ __('Actions') }}</th>
									</tr>
								</thead>
								<tbody>
									@foreach($marks as $index => $mark)
										<tr class="bg-white border-b hover:bg-gray-50 transition">
											<td class="px-6 py-3 font-medium text-gray-900">{{ $index + 1 }}</td>
											<td class="px-6 py-3 whitespace-nowrap">{{ $mark->enrollment?->student?->student_id ?? '-' }}</td>
											<td class="px-6 py-3 whitespace-nowrap capitalize">{{ $mark->enrollment?->student?->full_name ?? '-' }}</td>
											<td class="px-6 py-3 whitespace-nowrap">{{ $mark->assessment?->course?->course_code ?? '-' }} {{ $mark->assessment?->course?->title ? '- ' . $mark->assessment->course->title : '' }}</td>
											<td class="px-6 py-3 whitespace-nowrap">{{ $mark->assessment?->title ?? '-' }}</td>
											<td class="px-6 py-3">{{ number_format((float) $mark->score, 2) }}</td>
											<td class="px-6 py-3">
												@if($mark->is_locked)
													<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">{{ __('Locked') }}</span>
												@else
													<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">{{ __('Editable') }}</span>
												@endif
											</td>
											<td class="px-6 py-3 whitespace-nowrap">
												@if($mark->is_locked)
													<form action="{{ route('marks.request_edit', $mark->id) }}" method="POST" class="inline">
														@csrf
														<button type="submit" class="inline-flex items-center px-3 py-1.5 bg-amber-100 text-amber-700 rounded-md hover:bg-amber-200 transition font-medium text-xs mb-1 md:mb-0">
															{{ __('Request Edit') }}
														</button>
													</form>
													<button type="button" disabled class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-400 rounded-md font-medium text-xs cursor-not-allowed">
														{{ __('Delete') }}
													</button>
												@else
													<a href="{{ route('marks.edit', $mark->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition font-medium text-xs mb-1 md:mb-0">
														{{ __('Edit') }}
													</a>
													<form action="{{ route('marks.destroy', $mark->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this mark?') }}');">
														@csrf
														@method('DELETE')
														<button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition font-medium text-xs">
															{{ __('Delete') }}
														</button>
													</form>
												@endif
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					@else
						<div class="text-center py-12">
							<h3 class="text-lg font-semibold text-gray-900">{{ __('No marks found') }}</h3>
							<p class="mt-2 text-sm text-gray-500">{{ __('Enter your first mark to get started.') }}</p>
							<a href="{{ route('marks.create') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
								{{ __('Enter Mark') }}
							</a>
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>

	<script type="module">
		document.addEventListener('DOMContentLoaded', function () {
			['success-message', 'warning-message'].forEach(function (id) {
				const element = document.getElementById(id);
				if (element) {
					setTimeout(function () {
						element.remove();
					}, 5000);
				}
			});
		});
	</script>
</x-app-layout>
