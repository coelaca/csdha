@use('App\Models\Permission')
<x-layout.user form class="positions form" title="Edit position" route="positions.index">
<div class="mt-form-panel">
	<x-alert/>
	<div class="row">
		<form class="col s12" action="{{ route('positions.update', ['position' => $position->id ], false) }}" method="POST">
			@method('PUT')
			@csrf
			<div class="row">
				<p class="input-field col s7 m9">
					<input name="position_name" type="text" value="{{ old('position_name') ?? $position->name }}"
					@cannot('rename', $position)
						readonly
					@endcannot
					>
					<label>Position name</label>
				</p>
				<p class="input-field col s5 m3">
					<input type="number" name="position_order" value="{{ old('position_order') ?? $position->position_order }}">
					<label>Position order</label>
				</p>
			</div>
			<div class="row">
				<p class="input-field col s12">
					<select name="officer">
						<option value="" disabled>Choose officer</option>
					@can('removeOfficer', $position)
						<option value="0">None</option>
					@endcan
					@if ($officer)				
						<option value="{{ $officer->public_id }}" selected>{{ $officer->fullName }}</option>
					@endif
					@foreach ($users as $user)
						<option value="{{ $user->public_id }}">{{ $user->fullName }}</option>
					@endforeach
					</select>
					<label>Officer</label>
				</p>
			</div>
			<div class="row">
				<p class="col s12 mt-form-title"><label>Permissions</label></p>
			</div>
			@foreach ($resources as $resource)
			<div class="row">
				<fieldset>
					<legend>
						{{ ucwords(str_replace('-', ' ', $resource->name)) }}
					</legend>
					<div class="mt-checkbox-inline">
					@foreach($resource->actions as $action)
						<p class="checkbox">
							<label>
								<input class="filled-in" id="perm-{{ $action->permission->id }}" name="permissions[]" type="checkbox" value="{{ $action->permission->id }}"
									{{ $position->permissions()->whereKey($action->permission->id)->exists() ? 'checked' : '' }}
									@cannot ('changePerm', [$position, $action->permission])
									disabled
									@endcannot
								>
								<span for="perm-{{ $action->permission->id }}">
									{{ ucwords(str_replace('-', ' ', $action->name)) }}
								</span>
							</label>
						</p>
					@endforeach
					</div>
				</fieldset>
			</div>
			@endforeach
			<p class="form-button">
				<button class="btn waves-effect waves-light" form="delete-form"
				@cannot ('delete', $position)
					disabled
				@endcannot
				>Delete</button>
				<button class="btn waves-effect waves-light">Update</button>
			</p>
		</form>
		<form style="display:none;" id="delete-form" action="{{ route('positions.confirmDestroy', ['position' => $position->id]) }}">
			{{--
			<p class="form-submit">
				<button 
				@cannot ('delete', $position)
					disabled
				@endcannot
				>Delete position</button>
			</p>
			--}}
		</form>
	</div>
</div>
</x-layout>
