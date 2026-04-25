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
<body class="{{ $index ? 'index' : null }} {{ $form ? 'form' : null }}">
	<div class="navbar-fixed">
		<nav>
			<div class="nav-wrapper">
			@if ($index)
				<a href="#" data-target="slide-out" class="sidenav-trigger">
					<i class="material-icons">menu</i>
				</a>
			@elseif ($backRoute)
				<a href="{{ $backRoute }}" class="back-button">
					<i class="material-icons">arrow_back</i>
				</a>
			@elseif ($route)
				<a href="{{ route($route, $routeParams) }}" class="back-button">
					<i class="material-icons">arrow_back</i>
				</a>
			@endif
				<span class="mt-nav-title">{{ $title }}</span>
			@if (isset($toolbar) && !$toolbar->isEmpty())
				<ul class="right hide-on-med-and-down">
				{{ $toolbar }}
				</ul>
			@endif
			</div>
		</nav>
	</div>

@if ($index)
	<ul id="slide-out" class="sidenav sidenav-fixed">
		<li><div class="user-view">
			<!--div class="background">
				<img src="images/office.jpg">
			</div-->
			<a href="#user"><img class="circle" src="images/yuna.jpg"></a>
			<a href="#name"><span class="black-text name">John Doe</span></a>
			<a href="#email"><span class="black-text email">jdandturk@gmail.com</span></a>
		</div></li>
		<li><div class="divider"></div></li>
		<li class="{{ Format::currentIndex($userHomeRoute) ? 'active' : null }}">
			<a class="waves-effect" href="{{ $userHomeRoute }}">
				<i class="material-icons">home</i>
				Home
			</a>
		</li>
		<li class="{{ Format::currentIndex($gpoaRoute) ? 'active' : null }}">
			<a class="waves-effect" href="{{ $gpoaRoute }}">
				<i class="material-icons">ballot</i>
				GPOA
			</a>
		</li>
		<li>
			<a class="waves-effect" href="#!">
				<i class="material-icons">event</i>
				Events
			</a>
		</li>
		<li>
			<a class="waves-effect" href="#!">
				<i class="material-icons">article</i>
				Accom. Reports
			</a>
		</li>
		<li>
			<a class="waves-effect" href="#!">
				<i class="material-icons">person_pin</i>
				Attendance
			</a>
		</li>
		<li class="{{ Format::currentIndex($positionsRoute) ? 'active' : null }}">
			<a class="waves-effect" href="{{ $positionsRoute }}">
				<i class="material-icons">groups</i>
				Council Body
			</a>
		</li>
		<li>
			<a class="waves-effect" href="{{ $userSignoutRoute }}">
				<i class="material-icons">logout</i>
				Log out
			</a>
		</li>
	</ul>
@endif
	<main {{ $attributes }}>
		{{ $slot }}
	</main>
</body> 
</html>
