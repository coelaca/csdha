<x-layout.user content-view title="Sign-up Invites" :$backRoute class="accounts signup-invitation form">
<x-slot:toolbar>
	<a id="signup-invite_create-button" href="{{ $createRoute }}">
		<img class="icon" src="{{ asset('icon/light/plus.svg') }}">

		<span class="text">Create invite</span>
	</a>
</x-slot:toolbar>
<div class="article">
	<x-alert/>
@if ($invites->isNotEmpty())
	<ul class="item-list" id="signup-invite-items">
	@foreach ($invites as $invite)
		<li class="item">
			<div class="content">
				<p id="signup-invite-{{ $invite->id }}">{{ $invite->position?->name ?? 'No position'}}</p>
				<p>{{ $invite->email }}</p>
				<p>Status: 
					<span id="signup-invite-{{ $invite->id }}-status">
					@switch ($invite->email_sent)
					@case (null)
						Sending email
						@break
					@case (1)
						Email sent
						@break
					@case (0)
						Email not sent
						@break
					@endswitch
					</span>
				</p>
			</div>
			<div class="context-menu">
				<form action="{{ route('accounts.signup-invites.confirm-destroy', ['invite' => $invite->id]) }}">
					<button id="signup-invite-{{ $invite->id }}_delete-button" data-action="{{ route('accounts.signup-invites.destroy', ['invite' => $invite->id]) }}">Revoke</button>
				</form>
			</div>
		</li>
	@endforeach
	</ul>
@else 
	<p>Nothing here yet.</p>
@endif
</div>
<x-window class="form" id="signup-invite_create" title="Create Sign-up Invite">
	<form method="post" action="{{ $createFormAction }}">
	@csrf
		<p>
			<label>Council Body Position</label>
			<select required name="position">
				<option value="">-- Select position --</option>
				<option value="0" {{ old('position') === '0' ? 'selected' : null }}>
					No position
				</option>
			@foreach ($positions as $position)
				<option value="{{ $position->id }}" {{ old('position') === (string) $position->id ? 'selected' : null }}>
					{{ $position->name }}
				</option>
			@endforeach
			</select>
		</p>
		<p>
			<label>Email address</label>
			<input required type="email" name="email" value="{{ old('email') }}">
		</p>
		<p class="form-submit">
			<button id="signup-invite_create_close" type="button">Cancel</button>
			<button>Send</button>
		</p>
	</form>
</x-window>
<x-window class="form" id="signup-invite_delete" title="Revoke Sign-up Invite">
	<p>
		Are you sure you want to revoke this sign up invitation for <strong id="signup-invite_delete-content"></strong>?
	</p> 
	<div class="submit-buttons">
		<button id="signup-invite_delete_close">Cancel</button>
		<button form="delete-form">Revoke</button>
	</div>
	<form id="delete-form" method="post"> 
	@method('DELETE') 
	@csrf 
	</form>
</x-window>
</x-layout.user>
