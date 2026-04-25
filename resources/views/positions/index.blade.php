<x-layout.user has-toolbar index title="Central Body" class="index positions">
@can ('create', 'App\Models\Position')
<div class="fixed-action-btn">
	<a title="Add new officer position" href="{{ route('positions.create') }}" class="btn-floating btn-large">
		<i class="material-icons">add</i>
	</a>
</div>
@endcan
<div class="mt-main-table-panel">
@if ($positions->isNotEmpty())
	<table class="main-table table-2">
		<colgroup>
			<col style="width: 30%">
			<col style="width: 70%">
		</colgroup>
		<thead>
			<tr>
				<th>Position</th>
				<th>Officer Name</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($positions as $position)
			<tr>
				<td class="position-name">
				@can ('create', 'App\Models\Position')
					<a href="{{ route('positions.show', ['position' => $position->id]) }}">{{ $position->name }}</a>
				@else
					{{ $position->name }}
				@endcan
				</td>
				<td>{{ $position->user->full_name ?? '' }}</td>
			</tr>
		@endforeach
		</tbody>
	</table>
@else
	<p>No one has added anything yet</p>
@endif
</div>
</x-layout.user>
