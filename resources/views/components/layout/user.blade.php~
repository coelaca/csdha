@use ('Illuminate\Support\Facades\Route')
@use ('App\Services\Format')
@use ('App\Models\Event')
@php
$adminHomeRoute = route('admin.home'); 
$userHomeRoute = route('user.home'); 
$gpoaRoute = route('gpoa.index'); 
$eventsRoute = route('events.index');
if (!Event::active()->ongoingAndUpcoming()->exists()) {
    $eventsRoute = route('events.index', [
        'status' => 'completed',
    ]);
}
$accomReportsRoute = route('accom-reports.index');
$positionsRoute = route('positions.index');
$attendanceRoute = route('attendance.create');
$settingsRoute = route('settings.index');
$userSignoutRoute = route('user.logout');
$adminSignoutRoute = route('admin.logout');
$rolesRoute = route('roles.index');
$accountsRoute = route('accounts.index');
$auditRoute = route('audit.index');
$hasToolbar = isset($toolbar) && $toolbar->hasActualContent();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="color-scheme" content="only light">
	<meta name="csrf-token" content="{{ csrf_token() }}">
@switch ($siteContext)
@case ('user')
	<title>{{ $title }} - CSDHA</title>
	@break
@case ('admin')
	<title>{{ $title }} - CSDHA Admin</title>
	@break
@endswitch
	<link rel="icon" href="{{ asset('favicon.png') . '?id=' . cache('website_logo_id') }}" />
	@vite_legacy('resources/js/app-legacy.js')
	@vite(['resources/scss/app.scss', 'resources/js/app.js', ]) 
@if ($style)
	@vite(['resources/scss/' . $style])
@endif
</head>
<body class="main-body {{ $index ? 'index' : null }} {{ $form || $contentView ? 'form view' : null }} {{ $hasToolbar ? 'has-toolbar' : null }}">
@if ($index)
	<div class="main-header" id="menu">
		<a href="#" class="close-menu-button">
			<span class="text">Close Menu</span>
		</a>
		<div class="content-block">
			<div class="main-header-title">
				<div class="main-brand">
					<img class="logo" src="{{ asset('storage/website-logo.png') . '?id=' . cache('website_logo_id') }}">

					<span class="name">CSDHA</span>
				</div>
				<div class="main-account-link">
						<a href="{{ route('profile.edit') }}">
							<img class="icon" src="{{ asset('icon/dark/user-circle.png') }}">
							<span class="text">Account</span>
						</a>
				</div>
			</div>
			<div class="main-header-info">
				<div class="account">
					<div class="info">
						<div class="avatar">
						@if (auth()->user()->avatar_filepath)
							@if ($siteContext === 'user')
							<img src="{{ route('profile.showAvatar', ['avatar' => basename(auth()->user()->avatar_filepath)]) }}">
							@elseif ($siteContext === 'admin')
							<img src="{{ route('admin-profile.showAvatar', ['avatar' => basename(auth()->user()->avatar_filepath)]) }}">
							@endif
						@else
							<img src="{{ asset('icon/user.svg') }}">
						@endif
						</div>
						<div class="details">
							<p class="name">{{ auth()->user()->full_name }}</p>
						@if ($siteContext === 'admin')
							<p class="position">{{ ucwords(auth()->user()->role?->name) }}</p>
						@elseif ($siteContext === 'user')
							<p class="position">{{ auth()->user()->position?->name }}</p>
						@endif
						</div>
					</div>
				@if ($siteContext === 'user')
					<p class="main-action">
						<a href="{{ route('profile.edit') }}">
							<img class="icon" src="{{ asset('icon/dark/pencil-simple-line.svg') }}">
							<span class="text">Edit account</span>
						</a>
					</p>
				@endif
				</div>
			</div>
			<div class="main-header-menu">
				<p class="title">Menu</p>
				<ul class="list">
				@if ($siteContext === 'user')
					<li class="{{ Format::currentIndex($userHomeRoute) ? 'current-page' : null }}">
						<a href="{{ $userHomeRoute }}">
							<img class="icon" src="{{ asset('icon/dark/house.svg') }}">
							<span class="text">Home</span>
						</a>
					</li>
					@can ('viewAny', 'App\Models\Gpoa')
					<li class="{{ Format::currentIndex($gpoaRoute) ? 'current-page' : null }}">
						<a href="{{ $gpoaRoute }}">
							<img class="icon" src="{{ asset('icon/dark/blueprint.svg') }}">
							<span class="text">GPOA</span>
						</a>
					</li>
					@endcan
					@can ('viewAny', 'App\Models\Event')
					<li class="{{ Format::currentIndex($eventsRoute) ? 'current-page' : null }}">
						<a href="{{ $eventsRoute }}">
							<img class="icon" src="{{ asset('icon/dark/calendar.svg') }}">
							<span class="text">Events</span>
						</a>
					</li>
					@endcan
					@can ('viewAnyAccomReport', 'App\Models\Event')
					<li class="{{ Format::currentIndex($accomReportsRoute) ? 'current-page' : null }}">
						<a href="{{ $accomReportsRoute }}">
							<img class="icon" src="{{ asset('icon/dark/files.svg') }}">
							<span class="text">Accom. Reports</span>
						</a>
					</li>
					@endcan
					{{--
					@can ('viewAny', 'App\Models\Student')
					<li>
						<a href="{{ route('students.index') }}">
							<span class="icon"><x-phosphor-student/></span>
							<span class="text">Students</span>
						</a>
					</li>
					@endcan
					--}}
					@can ('viewAny', 'App\Models\Position')
					<li class="{{ Format::currentIndex($positionsRoute) ? 'current-page' : null }}">
						<a href="{{ $positionsRoute }}">
							<img class="icon" src="{{ asset('icon/dark/users-three.svg') }}">
							<span class="text">Central Body</span>
						</a>
					</li>
					@endcan
					@can ('viewAttendance', 'App\Models\Event')
					<li class="{{ Format::currentIndex($attendanceRoute) ? 'current-page' : null }}">
						<a href="{{ $attendanceRoute }}">
							<img class="icon" src="{{ asset('icon/dark/user-check.svg') }}">
							<span class="text">Attendance</span>
						</a>
					</li>
					@endcan
					@if (auth()->user()->hasPerm('settings.view'))
					<li class="{{ Format::currentIndex($settingsRoute) ? 'current-page' : null }}">
						<a href="{{ $settingsRoute }}">
							<img class="icon" src="{{ asset('icon/dark/wrench.svg') }}">
							<span class="text">Settings</span>
						</a>
					</li>
					@endif
					<li class="{{ Format::currentIndex($userSignoutRoute) ? 'current-page' : null }}">
						<a href="{{ $userSignoutRoute }}">
							<img class="icon" src="{{ asset('icon/dark/sign-out.svg') }}">
							<span class="text">Sign out</span>
						</a>
					</li>
				@elseif ($siteContext === 'admin')
					<li class="{{ Format::currentIndex($adminHomeRoute) ? 'current-page' : null }}">
						<a href="{{ $adminHomeRoute }}">
							<img class="icon" src="{{ asset('icon/dark/house.svg') }}">
							<span class="text">Home</span>
						</a>
					</li>
					<li class="{{ Format::currentIndex($auditRoute) ? 'current-page' : null }}">
						<a href="{{ $auditRoute }}">
							<img class="icon" src="{{ asset('icon/dark/table.svg') }}">
							<span class="text">Audit Trail</span>
						</a>
					</li>
					{{--
					<li>
						<a href="{{ route('analytics.index') }}">
							<img class="icon" src="{{ asset('icon/dark/chart-line.svg') }}">
							<span class="text">Analytics</span>
						</a>
					</li>
					--}}
					<li class="{{ Format::currentIndex($accountsRoute) ? 'current-page' : null }}">
						<a href="{{ $accountsRoute }}">
							<img class="icon" src="{{ asset('icon/dark/user-square.svg') }}">
							<span class="text">Accounts</span>
						</a>
					</li>
					<li class="{{ Format::currentIndex($rolesRoute) ? 'current-page' : null }}">
						<a href="{{ $rolesRoute }}">
							<img class="icon" src="{{ asset('icon/dark/user-gear.svg') }}">
							<span class="text">Roles</span>
						</a>
					</li>
					<li class="{{ Format::currentIndex($adminSignoutRoute) ? 'current-page' : null }}">
						<a href="{{ $adminSignoutRoute }}">
							<img class="icon" src="{{ asset('icon/dark/sign-out.svg') }}">
							<span class="text">Sign out</span>
						</a>
					</li>
				@endif
				</ul>
			</div>
		</div>
	</div>
	@endif
	<div class="main-main">
		<main> 
			<div class="main-content-header">
				<div class="content-block">
				@if ($index || $backRoute || $route)
					<div class="nav-actions">
					@if ($index)
						<a href="#menu" class="main-menu-button">
							<img alt="Menu" class="icon" src="{{ asset('icon/light/list.svg') }}">
							<span class="text">Menu</span>
						</a>
					@elseif ($backRoute)
						<a id="main-back-link" class="main-back-link" href="{{ $backRoute }}" >
							<img alt="Back" class="icon" src="{{ asset('icon/light/caret-left.svg') }}">

							<span class="text">Back to previous page</span>
						</a>
					@elseif ($route)
						<a id="main-back-link" class="main-back-link" href="{{ route($route, $routeParams) }}" >
							<img alt="Back" class="icon" src="{{ asset('icon/light/caret-left.svg') }}">
							<span class="text">Back to previous page</span>
						</a>
					@endif
					</div>
				@endif
					<h1 title="{{ $title }}" class="title">{{ $title ?? 'CSDHA' }}</h1>
				</div>
			</div>
			<div {{ $attributes->merge(['class' => 'main-content']) }}>
			@if ($hasToolbar)
				<div class="main-toolbar">
					<div class="content-block">
						{{ $toolbar }}
					</div>
				</div>
			@endif
				<div class="content-block">
					{{ $slot }}
				</div>
			</div>
		</main>
	</div>
</body>
</html>
