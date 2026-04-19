<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-gray-800 leading-tight">
			{{ __('Assessments') }}
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

			@if($assessments->count() > 0)
			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
				<div class="p-6 text-gray-900">
					<div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
						<div>
							<h3 class="text-lg font-semibold text-gray-900">{{ __('Manage Assessments') }}</h3>
							<p class="text-sm text-gray-500 mt-1">{{ __('View, show, and delete assessment records.') }}</p>
							
						</div>
						@can('create assessments')
						<a href="{{ route('assessments.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
							{{ __('Add Assessment') }}
						</a>
						@endcan
					</div>
				</div>
			</div>
			@endif

			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
				<div class="p-6 text-gray-900">
					@if(count($courses))
						<div class="mb-4 inline-flex items-center gap-2">
							<label for="course_filter" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Filter by Course') }}</label>
							<select id="course_filter" class="uppercase px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="if(this.value) { window.location.href = '{{ route('assessments.index') }}?course_id=' + this.value; } else { window.location.href = '{{ route('assessments.index') }}'; }">
								<option value="">{{ __('All Courses') }}</option>
								@foreach($courses as $course)
									<option value="{{ $course->id }}" {{ $courseId == $course->id ? 'selected' : '' }}>{{ $course->course_code }} - {{ $course->title }}</option>
								@endforeach
							</select>
						</div>
					@endif

					@if($assessments->count())
						<div class="overflow-x-auto">
							<table class="w-full text-sm text-left text-gray-600">
								<thead class="text-xs uppercase bg-gray-100 text-gray-700">
									<tr>
										<th class="px-6 py-2">{{ __('#') }}</th>
										<th class="px-6 py-2">{{ __('Course') }}</th>
										<th class="px-6 py-2">{{ __('Title') }}</th>
										<th class="px-6 py-2">{{ __('Max Score') }}</th>
										<th class="px-6 py-2">{{ __('Weight (%)') }}</th>
										<th class="px-6 py-2">{{ __('Actions') }}</th>
									</tr>
								</thead>
								<tbody>
									@foreach($assessments as $index => $assessment)
										<tr class="bg-white border-b hover:bg-gray-50 transition">
											<td class="px-6 py-2 font-medium text-gray-900">{{ $index + 1 }}</td>
											<td class="px-6 py-2 whitespace-nowrap uppercase">{{ $assessment->course?->course_code ?? '-' }} {{ $assessment->course?->title ? '- ' . $assessment->course->title : '' }}</td>
											<td class="px-6 py-2 whitespace-nowrap capitalize">{{ $assessment->title }}</td>
											<td class="px-6 py-2 whitespace-nowrap">{{ number_format((float) $assessment->max_score, 2) }}</td>
											<td class="px-6 py-2 whitespace-nowrap">{{ number_format((float) $assessment->weight, 2) }}</td>
											<td class="px-6 py-2 whitespace-nowrap">
												<a href="{{ route('assessments.show', $assessment->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition font-medium text-xs mb-1 md:mb-0">
													{{ __('Show') }}
												</a>
												@can('delete assessments')
												<form action="{{ route('assessments.destroy', $assessment->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this assessment?') }}');">
													@csrf
													@method('DELETE')
													<button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition font-medium text-xs">
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
							<h3 class="text-lg font-semibold text-gray-900">{{ __('No assessments found') }}</h3>
							<p class="mt-2 text-sm text-gray-500">{{ __('Create a new assessment to get started.') }}</p>
							@can('create assessments')
							<a href="{{ route('assessments.create') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
								{{ __('Add Assessment') }}
							</a>
							@endcan
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>

	<script type="module">
		document.addEventListener('DOMContentLoaded', function () {
			const successMessage = document.getElementById('success-message');
			if (successMessage) {
				setTimeout(() => {
					successMessage.remove();
				}, 5000);
			}
		});
	</script>
</x-app-layout>
