<x-layout.user form class="positions form" title="Create position" route="positions.index">
<div class="mt-form-panel">
	<x-alert/>
	<div class="row">
		<form class="col s12" action="{{ route('positions.store') }}" method="POST">
			@csrf
			<div class="row">
				<p class="input-field col s7 m9">
					<input type="text" id="position_name" name="position_name" value="{{ old('position_name') }}">
					<label for="position_name">Position name</label>
				</p>
				<p class="input-field col s5 m3">
					<input type="number" id="position_order" name="position_order" value="{{ old('position_order') }}">
					<label for="position_order">Position order</label>
				</p>
			</div>
			<div class="row">
				<p class="input-field col s12">
					<select name="officer">
						<option value="" disabled>Choose officer</option>
						<option value="0" {{ old('officer') === "0" ? 'selected' : null }}>None</option>
					@foreach ($users as $user)
						<option value="{{ $user->public_id }}" {{ old('officer') === (string) $user->public_id ? 'selected' : null }}>{{ $user->fullName }}</option>
					@endforeach
					</select>
					<label>Officer</label>
				</p>
			</div>
			<div class="row">
				<p class="mt-form-title col s12"><label>Permissions</label></p>
			</div>
			@foreach ($resources as $resource)
			<div class="row">
				<fieldset class="col s12">
					<legend>
						{{ ucwords(str_replace('-', ' ', $resource->name)) }}
					</legend>
					<div class="mt-checkbox-inline">
					@foreach($resource->actions as $action)
						<p class="checkbox">
							<label>
								<input class="filled-in" id="perm-{{ $action->permission->id }}" name="permissions[]" type="checkbox" value="{{ $action->permission->id }}" {{ in_array($action->permission->id, (old('permissions') ?? [])) ? 'checked' : null }}
									@cannot('addPerm', $action->permission)
									disabled
									@endcannot
								>
								<span for="perm-{{ $action->permission->id }}">{{ ucwords(str_replace('-', ' ', $action->name)) }}</span>
							</label>
						</p>
					@endforeach
					</div>
				</fieldset>
			</div>
			@endforeach
			<p class="form-button">
				<button class="btn waves-effect waves-light">Save</button>
			</p>
		</form>
	</div>
</div>
</x-layout>
