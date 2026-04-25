<x-layout.user form route="gpoa.index" title="{{ $update ? 'Edit' : 'Create' }} GPOA" class="gpoa form">
<div class="mt-form-panel">
	<x-alert type="error"/>
	<div class="row">
		<form class="col s12" method="post" action="{{ $update ? route('gpoa.update') : route('gpoa.store') }}">
		@if ($update)
		@method('PUT')
		@endif
		@csrf
			<div class="row">
				<fieldset class="col s12">
					<legend>Academic Term</legend>
					@foreach ($terms as $term)
						<p>
							<label class="radio-button">
								<input id="academic-term-{{ $term->id }}" type="radio" name="academic_term" value="{{ $term->id }}" {{ (old('academic_term') ?? (string)$gpoa?->academicPeriod->term->id) === (string)$term->id ? 'checked' : null }}>
								<span>{{ $term->label }}</span>
							</label>
						</p>
					@endforeach
				</fieldset>
			</div>
			<div class="row">
				<p class="input-field col s6">
					<input required placeholder="yyyy-mm-dd" type="date" name="start_date" value="{{ $errors->any() ? old('start_date') : $gpoa?->academicPeriod->start_date }}">
					<label>Academic start date</label>
				</p>
				<p class="input-field col s6">
					<input required placeholder="yyyy-mm-dd" type="date" name="end_date" value="{{ $errors->any() ? old('end_date') : $gpoa?->academicPeriod->end_date }}">
					<label>End date</label>
				</p>
			</div>
			<div class="row">
				<p class="input-field col s12">
					<input type="text" id="head_of_student_services" required maxlength="100" name="head_of_student_services" value="{{ $errors->any() ? old('head_of_student_services') : $gpoa?->academicPeriod->head_of_student_services }}">
					<label for="head_of_student_services">Head of Student Services</label>
				</p>
			</div>
			<div class="row">
				<p class="input-field col s12">
					<input id="branch_director" type="text" required maxlength="100" name="branch_director" value="{{ $errors->any() ? old('branch_director') : $gpoa?->academicPeriod->branch_director }}">
					<label for="branch_director">Branch Director</label>
				</p>
			</div>
            <p class="form-button">
                <button class="btn waves-effect waves-light">{{ $update ? 'Update' : 'Create' }}</button>
            </p>
		</form>
	</div>
</div>
</x-layout.user>
