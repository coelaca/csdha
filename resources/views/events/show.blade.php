<x-layout.user has-toolbar form :$backRoute class="events event form" title="Event">
<x-slot:toolbar>
	<a
	@can ('update', $event)
		href="{{ $editRoute }}"
	@endcan
	>
		<img class="icon" src="{{ asset('icon/light/wrench.svg') }}">
		<span class="text">Settings</span>
	</a>
	<a
	@can ('viewAccomReport', $event)
		href="{{ $genArRoute }}"
	@endcan
	>
		<img class="icon" src="{{ asset('icon/light/file-text.svg') }}">
		<span class="text">View accom. report</span>
	</a>
</x-slot:toolbar>
<div class="article has-item-full-content document">
	<x-alert/>
	<div class="item-full-content"> 
	@if ($bannerFileRoute)
		<div class="banner">
			<div class="content-block">
				<img src="{{ $bannerFileRoute }}">
			</div>
	@else
		<div class="banner" style="background-color: {{ $event->banner_placeholder_color }};">
	@endif
		@can ('update', $event)
			<p class="banner-edit-link"><a id="event-banner_edit-button" href="{{ $bannerRoute }}">Edit</a></p>
		@endcan
		</div>
		<div class="content-block">
			<h2>{{ $activity->name }}</h2>
			<table class="main-table table-2">
				<colgroup>
					<col style="width: 12em;">
				</colgroup>
				<tr>
					<th>Date 
					@can ('update', $event)
						<span class="edit-link">[ <a href="{{ $dateRoute }}">Edit</a> ]</span>
					@endcan
					</th>
					<td>
						<ul>
						@foreach ($event->compactDates() as $date)
							<li>{{ $date }}</li>
						@endforeach
						</ul>
					</td>
				</tr>
			@if ($event->automatic_attendance)
				<tr>
					<th>Registration Form</th>
					<td><a href="{{ $regisRoute }}">Show</a></td>
				</tr>
				<tr>
					<th>Registration Form Link</th> 
					<td>
						<a href="{{ $regisFormRoute }}">
							{{ $regisFormRoute }}
						</a>
					</td>
				</tr>
			@endif
			@if ($event->accept_evaluation)
				<tr>
					<th>Evaluation Form 
					@can ('update', $event)
						<span class="edit-link">[ <a href="{{ $evalRoute }}">Edit</a> ]</span>
					@endcan
					</th>
					<td><a href="{{ $evalPreviewRoute }}">Show Preview</a></td>
				</tr>
				<tr>
					<th>Evaluation Comments</th>
					<td><a href="{{ $commentsRoute }}">Show</a></td>
				</tr>
			@endif
				<tr>
					<th>Description 
					@can ('update', $event)
						<span class="edit-link">[ <a id="event-description_edit-button" href="{{ $descriptionRoute }}">Edit</a> ]</span>
					@endcan
					</th>
					<td id="event-description"><pre>{{ $event->description }}</pre></td>
				</tr>
				<tr>
					<th>Narrative 
					@can ('update', $event)
						<span class="edit-link">[ <a id="event-narrative_edit-button" href="{{ $narrativeRoute }}">Edit</a> ]</span>
					@endcan
					</th>
					<td id="event-narrative"><pre>{{ $event->narrative }}</pre></td>
				</tr>
				<tr>
					<th>Venue 
					@can ('update', $event)
						<span class="edit-link">[ <a id="event-venue_edit-button" href="{{ $venueRoute }}">Edit</a> ]</span>
					@endcan
					</th>
					<td id="event-venue">{{ $event->venue }}</td>
				</tr>
			{{--
			@can ('evaluate', $event)
				<tr>
					<th>Evaluation Form 
					@can ('update', $event)
						<span class="edit-link">[ <a>Edit</a> ]</span>
					@endcan
					</th>
					<td><a href="{{ $evalPreviewRoute }}">Show preview</a></td>
				</tr>
				<tr>
					<th>Evaluation Result 
					@can ('update', $event)
						<span class="edit-link">[ <a>Edit</a> ]</span>
					@endcan
					</th>
					<td>0 comments selected</td>
				</tr>
			@endcan
			--}}
				<tr>
					<th>Attachments</th>
					<td><a href="{{ $attachmentRoute }}">Show</a></td>
				</tr>
			@can ('recordAttendance', $event)
				<tr>
					<th>Attendance</th>
					<td><a href="{{ $attendanceRoute }}">Show</a></td>
				</tr>
			@endcan
				<tr>
					<th>Participants / Beneficiaries</th>
					<td>{{ $activity->participants }}</td>
				</tr>
				<tr>
					<th>Type of Activity</th>
					<td>{{ $activity->type }}</td>
				</tr>
				<tr>
					<th>Objectives</th>
					<td><pre>{{ $activity->objectives }}</pre></td>
				</tr>
				<tr>
					<th>Links 
					@can ('update', $event)
						<span class="edit-link">[ <a href="{{ $linksRoute }}">Edit</a> ]</span>
					@endcan
					</th>
					<td>
						<ul>
						@foreach ($event->links as $link)
							<li><a href="{{ $link->url }}">{{ $link->name }}</a></li>
						@endforeach
						</ul>
					</td>
				</tr>
				<tr>
					<th>Event Head 
					@can ('update', $event)
						<span class="edit-link">[ <a id="event-heads_edit-button" href="{{ $eventHeadsRoute }}">Edit</a> ]</span>
					@endcan
					</th>
					<td>
						<ul>
						@foreach ($eventHeadList as $eventHead)
							<li>{{ $eventHead->full_name }}</li>
						@endforeach
						</ul>
					</td>
				</tr>
				<tr>
					<th>Co-head 
					@can ('update', $event)
						<span class="edit-link">[ <a id="event-coheads_edit-button" href="{{ $coheadsRoute }}">Edit</a> ]</span>
					@endcan
					</th>
					<td>
						<ul>
						@foreach ($coheadList as $cohead)
							<li>{{ $cohead->full_name }}</li>
						@endforeach
						</ul>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
<x-window class="form" id="event-description_edit" title="Edit event description">
	<form method="post" action="{{ $descriptionFormAction }}">
	@csrf
	@method('PUT')
		<p>
			<label for="event-description_field">Description</label>
			<textarea id="event-description_field" name="description">{{ old('description') }}</textarea>
		</p>
		<p class="form-submit">
			<button type="button" id="event-description_edit_close">Cancel</button>
			<button>Update</button>
		</p>
	</form>
</x-window>
<x-window class="form" id="event-narrative_edit" title="Edit event narrative">
	<form method="post" action="{{ $narrativeFormAction }}">
	@csrf
	@method('PUT')
		<p>
			<label for="event-narrative_field">Narrative</label>
			<textarea id="event-narrative_field" name="narrative">{{ old('narrative') }}</textarea> </p>
		<p class="form-submit">
			<button type="button" id="event-narrative_edit_close">Cancel</button>
			<button>Update</button>
		</p>
	</form>
</x-window>
<x-window class="form" id="event-venue_edit" title="Edit event venue">
	<form method="post" action="{{ $venueFormAction }}">
	@csrf
	@method('PUT')
		<p>
			<label for="event-venue_field">Venue</label>
			<input maxlength="255" id="event-venue_field" name="venue" value="{{ old('venue') }}">
		</p>
		<p class="form-submit">
			<button type="button" id="event-venue_edit_close">Cancel</button>
			<button>Update</button>
		</p>
	</form>
</x-window>
<x-window class="form" id="event-banner_edit" title="Edit event banner">
	<form method="post" action="{{ $bannerFormAction }}" enctype="multipart/form-data">
	@csrf
	@method('PUT')
		<p>
			<label for="banner">Banner</label>
			<input id="banner" name="banner" type="file" accept="image/jpeg, image/png, image/webp, image/avif">
		</p>
		<p class="checkbox">
			<input id="remove-banner" type="checkbox" name="remove_banner" value="1">
			<label for="remove-banner">Remove banner</label>
		</p>
		<p class="form-submit">
			<button type="button" id="event-banner_edit_close">Cancel</button>
			<button>Update</button>
		</p>
	</form>
</x-window>
<x-window class="form" id="event-heads_edit" title="Edit event head">
	<form method="post" action="{{ $eventHeadsFormAction }}">
	@csrf
	@method('PUT')
		<p>
			<label>Event Head</label>
			<select multiple size="5" name="event_heads[]">
			@if ($authUserIsEventHead))
				<option disabled value="">{{ auth()->user()->full_name }} (Added)</option>
			@endif
				<option value="0" 
				@if ($errors->any())
					{{ in_array('0', old('event_heads') ?? []) ? 'selected' : null }}
				@else
					{{ $allAreEventHeads ? 'selected' : null }}
				@endif
				>
					All CSCB Officers
				</option>
			@foreach ($selectedEventHeads as $selectedEventHead)
				<option value="{{ $selectedEventHead->public_id }}" selected>{{ $selectedEventHead->full_name }}</option>
			@endforeach
			@foreach ($eventHeads as $eventHead)
				<option value="{{ $eventHead->public_id }}">{{ $eventHead->full_name }}</option>
			@endforeach
			</select>
		</p>
		<p class="form-submit">
			<button id="event-heads_edit_close" type="button">Cancel</button>
			<button type="reset">Reset</button>
			<button>Update</button>
		</p>
	</form>
</x-window>
<x-window class="form" id="event-coheads_edit" title="Edit event co-head">
	<form method="post" action="{{ $coheadsFormAction }}">
	@csrf
	@method('PUT')
		<p>
			<label>Co-head (optional)</label>
			<select multiple size="5" name="coheads[]"> 
			@if ($authUserIsCohead))
				<option disabled value="">{{ auth()->user()->full_name }} (Added)</option>
			@endif
				<option value="0">None</option>
			@foreach ($selectedCoheads as $selectedCohead)
				<option value="{{ $selectedCohead->public_id }}" selected>{{ $selectedCohead->full_name }}</option>
			@endforeach
			@foreach ($coheads as $cohead)
				<option value="{{ $cohead->public_id }}">{{ $cohead->full_name }}</option>
			@endforeach
			</select>
		</p>
		<p class="form-submit">
			<button id="event-coheads_edit_close" type="button">Cancel</button>
			<button type="reset">Reset</button>
			<button>Update</button>
		</p>
	</form>
</x-window>
</x-layout.user>
