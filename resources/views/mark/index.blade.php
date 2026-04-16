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

			@if(session('error'))
				<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm font-medium">
					{{ session('error') }}
				</div>
			@endif

			@if($errors->any())
				<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
					<ul class="text-sm text-red-800 list-disc pl-5 space-y-1">
						@foreach($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif

			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
				<div class="p-6 text-gray-900">
					<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
						<div>
							<h3 class="text-lg font-semibold text-gray-900">{{ __('Entered Marks') }}</h3>
							<p class="text-sm text-gray-500 mt-1">{{ __('Only marks entered by your account are shown here.') }}</p>
						</div>
						<div class="flex flex-col md:flex-row md:items-center gap-2 w-full md:w-auto">
							<a href="{{ route('marks.sample_csv') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition whitespace-nowrap">
								{{ __('Download Sample CSV') }}
							</a>
							<form action="{{ route('marks.bulk_upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-center gap-2 w-full">
								@csrf
								<input type="file" name="marks_file" accept=".csv,text/csv" required class="block w-full text-sm text-gray-700 border border-gray-300 rounded-md cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
								<button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 transition whitespace-nowrap">
									{{ __('Upload Bulk List') }}
								</button>
							</form>
							<a href="{{ route('marks.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition whitespace-nowrap">
								{{ __('Enter Mark') }}
							</a>
						</div>
					</div>
				</div>
			</div>

			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
				<div class="p-6 text-gray-900">
					@if($marks->count())
					<div class="overflow-x-auto">
						<div class="flex flex-col md:flex-row space-x-2 gap-4">
							@if(count($courses))
							<div class="mb-4 inline-flex flex-col sm:flex-row sm:items-center gap-2">
								<label for="course_filter" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Filter by Course') }}</label>
								<select id="course_filter" class="w-full uppercase px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="const assessmentFilter = document.getElementById('assessment_filter'); if (assessmentFilter) { assessmentFilter.value = ''; } updateFilters()">
									<option value="">{{ __('All Courses') }}</option>
									@foreach($courses as $course)
										<option value="{{ $course->id }}" {{ $courseId == $course->id ? 'selected' : '' }}>{{ $course->course_code }}</option>
									@endforeach
								</select>
							</div>
							@endif
							<div class="mb-4 inline-flex flex-col sm:flex-row sm:items-center gap-2">
								<label for="assessment_filter" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Filter by Assessment') }}</label>
								<select id="assessment_filter" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed" onchange="updateFilters()" {{ $courseId ? '' : 'disabled' }}>
									<option value="">{{ __('All Assessments') }}</option>
									@foreach($assessments as $assessment)
										<option value="{{ $assessment->id }}" {{ $assessmentId == $assessment->id ? 'selected' : '' }}>{{ $assessment->title }}</option>
									@endforeach
								</select>
								@if(!$courseId)
									<p class="text-xs text-gray-500 mt-2">{{ __('Select a course first to load assessment titles.') }}</p>
								@endif
							</div>
						</div>
							<table class="w-full text-sm text-left text-gray-600">
								<thead class="text-xs uppercase bg-gray-100 text-gray-700">
									<tr>
										<th class="px-6 py-3">{{ __('#') }}</th>
										<th class="px-6 py-3 whitespace-nowrap">{{ __('Student ID') }}</th>
										{{-- <th class="px-6 py-3">{{ __('Student Name') }}</th> --}}
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
											<td class="px-6 py-2 font-medium text-gray-900">{{ $index + 1 }}</td>
											<td class="px-6 py-2 whitespace-nowrap">{{ $mark->enrollment?->student?->student_id ?? '-' }}</td>
											{{-- <td class="px-6 py-2 whitespace-nowrap capitalize">{{ $mark->enrollment?->student?->full_name ?? '-' }}</td> --}}
											<td class="px-6 py-2 whitespace-nowrap">{{ $mark->assessment?->course?->course_code ?? '-' }} {{ $mark->assessment?->course?->title ? '- ' . $mark->assessment->course->title : '' }}</td>
											<td class="px-6 py-2 whitespace-nowrap">{{ $mark->assessment?->title ?? '-' }}</td>
											<td class="px-6 py-2">{{ number_format((float) $mark->score, 2) }}</td>
											<td class="px-6 py-2">
												@if($mark->is_locked)
													<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">{{ __('Locked') }}</span>
												@else
													<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold">{{ __('Editable') }}</span>
												@endif
											</td>
											<td class="px-6 py-2 whitespace-nowrap">
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
							<p class="mt-2 text-sm text-gray-500">{{ __('Enter marks manually or upload a CSV list to get started.') }}</p>
							<div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
								<a href="{{ route('marks.sample_csv') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition whitespace-nowrap">
									{{ __('Download Sample CSV') }}
								</a>
								<form action="{{ route('marks.bulk_upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-2">
									@csrf
									<input type="file" name="marks_file" accept=".csv,text/csv" required class="block w-full text-sm text-gray-700 border border-gray-300 rounded-md cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
									<button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 transition whitespace-nowrap">
										{{ __('Upload Bulk List') }}
									</button>
								</form>
								<a href="{{ route('marks.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
									{{ __('Enter Mark') }}
								</a>
							</div>
							<p class="mt-3 text-xs text-gray-500">{{ __('CSV headers: student_id, course_code (or course_id), assessment_title (or assessment_id), score.') }}</p>
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>

	<script type="module">
		window.updateFilters = function () {
			const courseId = document.getElementById('course_filter')?.value || '';
			const assessmentId = document.getElementById('assessment_filter')?.value || '';
			const params = new URLSearchParams();
			if (courseId) params.append('course_id', courseId);
			if (assessmentId) params.append('assessment_id', assessmentId);
			const queryString = params.toString();
			window.location.href = queryString ? '{{ route('marks.index') }}?' + queryString : '{{ route('marks.index') }}';
		};

		document.addEventListener('DOMContentLoaded', function () {
			['success-message', 'warning-message'].forEach(function (id) {
				const element = document.getElementById(id);
				if (element) {
					setTimeout(function () {
						element.remove();
					}, 10000);
				}
			});
		});
	</script>
</x-app-layout>
