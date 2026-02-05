<x-layout.user content-view class="events form" :$backRoute title="Edit event heads">
<div class="article">
	<x-alert error-bag="event-heads_edit" />
	<form method="post" action="{{ $formAction }}">
	@csrf
	@method('PUT')
		<p>
			<label>Event Head</label>
			<select multiple size="5" name="event_heads[]">
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
			<button type="reset">Reset</button>
			<button>Update</button>
		</p>
	</form>
</div>
</x-layout>