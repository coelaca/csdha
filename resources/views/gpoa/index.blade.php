<x-layout.user has-toolbar index title="General Plan of Activities" class="form gpoa index">
@if ($gpoa)
	@can ('create', 'App\Models\GpoaActivity')
<div class="fixed-action-btn">
	<a title="Add Activity" href="{{ route('gpoa.activities.create') }}" class="btn-floating btn-large">
		<i class="material-icons">add</i>
	</a>
</div>
	@endcan
@else
	@can ('create', 'App\Models\Gpoa')
<div class="fixed-action-btn">
	<a href="{{ route('gpoa.create') }}" class="btn-floating btn-large">
		<i class="large material-icons">add</i>
	</a>
</div>
	@endcan
@endif

<x-slot:toolbar>
@if ($gpoa)
	@can ('update', 'App\Models\Gpoa')
	<li><a title="Edit" href="{{ route('gpoa.edit') }}">
		<i class="material-icons">add</i>
	</a></li>
	@endcan
	@can ('close', 'App\Models\Gpoa')
	<li><a title="View Report" href="{{ route('gpoa.showGenPdf') }}">
		<i class="material-icons">add</i>
	</a></li>
	@endcan
	<li><a data-target="dropdown1" class="dropdown-trigger" title="More Menu" href="#">
		<i class="material-icons">more_vert</i>
	</a></li>
@else
	<li><a title="Browse Closed GPOAs" href="{{ route('gpoas.old-index') }}">
		<i class="material-icons">archive</i>
	</a></li>
@endif
</x-slot:toolbar>

@if ($gpoa)
<ul id="dropdown1" class="dropdown-content">
	@can ('close', 'App\Models\Gpoa')
	<li><a id="gpoa_close-button" href="{{ route('gpoa.confirmClose') }}">
		<i class="material-icons">add</i>
		Close
	</a></li>
	@endcan
</ul>
@endif

<div class="mt-main-flat-panel">
@if (!$gpoa)
	<p>There is no active GPOA right now.</p>
@else
	@switch (auth()->user()->position_name)
	@case('president')
	@case('adviser')
	<p>No one has submitted anything yet.</p>
		@break
	@default
		<p>No one has added anything yet.</p>
	@endswitch
@endif
</div>

{{--
	<div class="article">
	@if (session('status') === 'returned')
		<x-alert>
			Activity returned to the {{ session('position') }}.
		</x-alert>
	@else
		<x-alert item-type="Activity"/>
	@endif

	@elseif ($gpoa && $activities?->isNotEmpty())
        <table class="main-table articles table-2">
            <colgroup>
                <col style="width: 30%">
                <col style="width: 70%">
            </colgroup>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($activities as $activity)
                <tr class="{{ $loop->last ? 'last-row' : null }}">
                    <td class="activity-name">
						<a href="{{ route('gpoa.activities.show', ['activity' => $activity->public_id]) }}">
                            {{ $activity->name }}
                        </a>
                    </td>
                    <td class="last-row-cell">
			<img class="icon" src="{{ asset("icon/small/light/circle-{$activity->status_color}.svg") }}">
			<span class="text">{{ $activity->full_status }}</span>
			</td>
                </tr>
            @endforeach
            </tbody>
        </table>
		{{ $activities->links('paginator.simple') }}
	</div>
@if ($gpoa)
<x-window class="form" id="gpoa_close" title="Close GPOA">
	<p>
		Are you sure you want to close this GPOA for {{ $gpoa->academicPeriod->term->label }} A.Y. {{ $gpoa->academicPeriod->year_label }}?
	</p>
	<div class="submit-buttons">
		<button id="gpoa_close_close">Cancel</button>
		<form method="post" action="{{ $closeRoute }}">
		@csrf
		@method('PUT')
			<button>Close GPOA</button>
		</form>
	</div>
</x-window>
@endif
--}}
</x-layout.user>
