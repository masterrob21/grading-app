<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-gray-800 leading-tight">
			{{ __('All Marks') }}
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
			
			@if ($marks->count()>0)	
			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
				<div class="p-6 text-gray-900">
					<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
						<div>
							<h3 class="text-lg font-semibold text-gray-900">{{ __('All Marks') }}</h3>
							<p class="text-sm text-gray-500 mt-1">{{ __('List of marks entered by all users.') }}</p>
						</div>
						
					</div>
				</div>
			</div>
			@endif

			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
				<div class="p-6 text-gray-900">
					@if($marks->count())
					@php
						$exportQuery = array_filter([
							'course_id' => $courseId,
							'assessment_id' => $assessmentId,
							'student_id' => $studentId ?? null,
						], fn ($value) => filled($value));
					@endphp
					<form id="bulk-lock-form" action="{{ route('marksheet.bulk_lock') }}" method="POST" class="hidden">
						@csrf
					</form>
					<form id="bulk-unlock-form" action="{{ route('marksheet.bulk_unlock') }}" method="POST" class="hidden">
						@csrf
					</form>
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
							<div class="mb-4 inline-flex flex-col sm:flex-row sm:items-center gap-2">
								<label for="student_id_filter" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Search Student ID') }}</label>
								<div class="flex items-center gap-2">
									<input id="student_id_filter" type="text" value="{{ $studentId ?? '' }}" placeholder="{{ __('e.g. STU001') }}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
									<button type="button" onclick="updateFilters()" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition whitespace-nowrap">
										{{ __('Search') }}
									</button>
								</div>
							</div>
							<div class="mb-4 inline-flex flex-col sm:flex-row sm:items-center gap-2">
								<span class="block text-sm font-medium text-gray-700 mb-2">{{ __('Export') }}</span>
								<div class="flex items-center gap-2">
									<a href="{{ route('marksheet.export.csv', $exportQuery) }}" class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition whitespace-nowrap">
										{{ __('CSV') }}
									</a>
								</div>
							</div>
						</div>
						<div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-2">
							<button id="bulk-lock-button" type="submit" form="bulk-lock-form" disabled class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 transition disabled:opacity-50 disabled:cursor-not-allowed" onclick="return confirm('{{ __('Lock all selected marks? This will prevent normal edits.') }}');">
								{{ __('Lock Selected') }}
							</button>
							<button id="bulk-unlock-button" type="submit" form="bulk-unlock-form" disabled class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 transition disabled:opacity-50 disabled:cursor-not-allowed" onclick="return confirm('{{ __('Unlock all selected marks? This will allow edits again.') }}');">
								{{ __('Unlock Selected') }}
							</button>
							<p class="text-xs text-gray-500">{{ __('Select rows, then lock or unlock them in bulk.') }}</p>
						</div>
							<table class="w-full text-sm text-left text-gray-600">
								<thead class="text-xs uppercase bg-gray-100 text-gray-700">
									<tr>
										<th class="px-6 py-3">
											<input id="select_all_marks" type="checkbox" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
										</th>
										<th class="px-6 py-3">{{ __('#') }}</th>
										<th class="px-6 py-3 whitespace-nowrap">{{ __('Student ID') }}</th>
										<th class="px-6 py-3">{{ __('User Name') }}</th>
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
											<td class="px-6 py-2">
												<input type="checkbox" name="mark_ids[]" value="{{ $mark->id }}" form="bulk-lock-form" class="mark-select-checkbox rounded border-gray-300 text-amber-600 focus:ring-amber-500">
												<input type="hidden" name="mark_ids[]" value="{{ $mark->id }}" form="bulk-unlock-form" class="mark-select-hidden" disabled>
											</td>
											<td class="px-6 py-2 font-medium text-gray-900">{{ $index + 1 }}</td>
											<td class="px-6 py-2 whitespace-nowrap">{{ $mark->enrollment?->student?->student_id ?? '-' }}</td>
											<td class="px-6 py-2 whitespace-nowrap">{{ $mark->user?->name ?? '-' }}</td>
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
													<form action="{{ route('marksheet.unlock', $mark->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Unlock this mark? This will allow edits again.') }}');">
														@csrf
														<button type="submit" class="inline-flex items-center px-3 py-1.5 bg-amber-100 text-amber-700 rounded-md hover:bg-amber-200 transition font-medium text-xs mb-1 md:mb-0">
															{{ __('Unlock') }}
														</button>
													</form>
												@else
													<form action="{{ route('marksheet.lock', $mark->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Lock this mark? This will prevent normal edits.') }}');">
														@csrf
														<button type="submit" class="inline-flex items-center px-3 py-1.5 bg-amber-100 text-amber-700 rounded-md hover:bg-amber-200 transition font-medium text-xs mb-1 md:mb-0">
															{{ __('Lock') }}
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
			const studentId = document.getElementById('student_id_filter')?.value.trim() || '';
			const params = new URLSearchParams();
			if (courseId) params.append('course_id', courseId);
			if (assessmentId) params.append('assessment_id', assessmentId);
			if (studentId) params.append('student_id', studentId);
			const queryString = params.toString();
			window.location.href = queryString ? '{{ route('marksheet.index') }}?' + queryString : '{{ route('marksheet.index') }}';
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

			const selectAllCheckbox = document.getElementById('select_all_marks');
			const selectableRows = Array.from(document.querySelectorAll('.mark-select-checkbox'));
			const unlockHiddenInputs = Array.from(document.querySelectorAll('.mark-select-hidden'));
			const bulkLockButton = document.getElementById('bulk-lock-button');
			const bulkUnlockButton = document.getElementById('bulk-unlock-button');

			const syncBulkLockState = function () {
				const checkedCount = selectableRows.filter(function (checkbox) {
					return checkbox.checked;
				}).length;

				selectableRows.forEach(function (checkbox, index) {
					if (unlockHiddenInputs[index]) {
						unlockHiddenInputs[index].disabled = ! checkbox.checked;
					}
				});

				if (bulkLockButton) {
					bulkLockButton.disabled = checkedCount === 0;
				}

				if (bulkUnlockButton) {
					bulkUnlockButton.disabled = checkedCount === 0;
				}

				if (selectAllCheckbox) {
					if (checkedCount === 0) {
						selectAllCheckbox.checked = false;
						selectAllCheckbox.indeterminate = false;
					} else if (checkedCount === selectableRows.length) {
						selectAllCheckbox.checked = true;
						selectAllCheckbox.indeterminate = false;
					} else {
						selectAllCheckbox.checked = false;
						selectAllCheckbox.indeterminate = true;
					}
				}
			};

			if (selectAllCheckbox) {
				selectAllCheckbox.addEventListener('change', function () {
					selectableRows.forEach(function (checkbox) {
						checkbox.checked = selectAllCheckbox.checked;
					});
					syncBulkLockState();
				});
			}

			selectableRows.forEach(function (checkbox) {
				checkbox.addEventListener('change', syncBulkLockState);
			});

			const studentIdFilter = document.getElementById('student_id_filter');
			if (studentIdFilter) {
				studentIdFilter.addEventListener('keydown', function (event) {
					if (event.key === 'Enter') {
						event.preventDefault();
						window.updateFilters();
					}
				});
			}

			syncBulkLockState();
		});
	</script>
</x-app-layout>
