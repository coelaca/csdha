<x-layout.user form :$backRoute title="{{ $activity ? 'Update' : 'Add' }} GPOA Activity" class="gpoa form">
<div class="mt-form-panel">
	<x-alert type="error"/>
	<div class="row">
		<form class="col s12" method="post" action="{{ $formAction }}">
		@if ($activity)
			@method('PUT')
		@endif
		@csrf
			<div class="row">
				<p class="input-field col s12">
					<input required maxlength="255" name="name" value="{{ $errors->any() ? old('name') : $activity?->name }}">
					<label>Name of Activity</label>
				</p>
			</div>
			<div class="row">
				<p class="input-field col s6">
					<input required placeholder="yyyy-mm-dd" type="date" pattern="^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$" name="start_date" value="{{ $errors->any() ? old('start_date') : $activity?->start_date }}">
					<label>Start Date</label>
				</p>
				<p class="input-field col s6">
					<label>End Date (optional)</label>
					<input placeholder="yyyy-mm-dd" type="date" pattern="^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$" name="end_date" value="{{ $errors->any() ? old('end_date') : $activity?->end_date }}">
				</p>
			</div>
			<div class="row">
				<p class="input-field col s12">
					<textarea class="materialize-textarea" required name="objectives">{{ $errors->any() ? old('objectives') : $activity?->objectives }}</textarea>
					<label>Objectives</label>
				</p>
			</div>
			<div class="row">
				<p class="input-field col s12">
					<input required maxlength="100" name="participants_description" value="{{ $errors->any() ? old('participants_description') : $activity?->participants }}">
					<label>Participants Description</label>
				</p>
			</div>
			<div class="row">
				<p class="input-field col s6">
					<input required maxlength="255" name="type_of_activity" list="activity_types" autocomplete="off" value="{{ $errors->any() ? old('type_of_activity') : $activity?->type }}">
					<label>Type of Activity</label>
					<datalist id="activity_types">
					@foreach ($activityTypes as $type)
						<option value="{{ $type->name }}">
					@endforeach
					</datalist>
				</p>
				<p class="input-field col s6">
					<input required maxlength="50" name="mode" list="modes" autocomplete="off" value="{{ $errors->any() ? old('mode') : $activity?->mode }}">
					<label>Mode</label>
					<datalist id="modes">
					@foreach ($modes as $mode)
						<option value="{{ $mode->name }}">
					@endforeach
					</datalist>
				</p>
			</div>
			<div class="row">
				<p class="input-field col s12">
					<input maxlength="255" name="partnership" list="partnership_types" autocomplete="off" value="{{ $errors->any() ? old('partnership') : $activity?->partnership_type }}">
					<label>Partnership (optional)</label>
					<datalist id="partnership_types">
					@foreach ($partnershipTypes as $type)
						<option value="{{ $type->name }}">
					@endforeach
					</datalist>
				</p>
			</div>
			<div class="row">
				<p class="input-field col s6">
					<input type="number" min="0" max="999999.99" step="100" name="proposed_budget" value="{{ $errors->any() ? old('proposed_budget') : $activity?->proposed_budget }}">
					<label>Proposed Budget (optional)</label>
				</p>
				<p class="input-field col s6">
					<input maxlength="255" name="fund_source" list="fund_sources" autocomplete="off" value="{{ $errors->any() ? old('fund_source') : $activity?->fund_source }}">
					<label>Source of Fund (optional)</label>
					<datalist id="fund_sources">
					@foreach ($fundSources as $source)
						<option value="{{ $source->name }}">
					@endforeach
					</datalist>
				</p>
			</div>
			@if (!$activity || ($activity && auth()->user()->can('updateEventHeads', $activity)))
				<p>
					<label>Event Head</label>
					<select multiple size="5" name="event_heads[]">
					@if (!$activity || ($activity && $authUserIsEventHead))
						<option disabled value="">{{ auth()->user()->full_name }} (Added)</option>
					@endif
						<option value="0" 
						@if ($errors->any())
							{{ in_array('0', old('event_heads') ?? []) ? 'selected' : null }}
						@else
							{{ $activity && $allAreEventHeads ? 'selected' : null }}
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
				<p>
					<label>Co-head (optional)</label>
					<select multiple size="5" name="coheads[]"> 
						<option value="0">None</option>
					@foreach ($selectedCoheads as $selectedCohead)
						<option value="{{ $selectedCohead->public_id }}" selected>{{ $selectedCohead->full_name }}</option>
					@endforeach
					@foreach ($coheads as $cohead)
						<option value="{{ $cohead->public_id }}">{{ $cohead->full_name }}</option>
					@endforeach
					</select>
				</p>
			@endif
			<p class="form-submit">
				<button>{{ $activity ? 'Update' : 'Add' }} Activity</button>
			</p>
		</form>
	</div>
</div>
</x-layout.user>
