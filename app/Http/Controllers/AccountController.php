<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\SignupInvitation;
use App\Models\Position;
use App\Mail\SignupInvitation as SignupInvitationMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\StoreSignupInvitationRequest;
use App\Http\Requests\UpdateAccountRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Jobs\SendSignupInvite;
use App\Services\Stream;

class AccountController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:delete,account', only: [
                'destroy', 'confirmDestroy'
            ]),
        ];
    }

    public function index()
    {
        $accounts = User::orderBy('first_name', 'asc')->paginate(15);
        return view('accounts.index', [
            'accounts' => $accounts
        ]);
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(User $account)
    {
        return view('accounts.show', [
            'account' => $account,
            'backRoute' => route('accounts.index'),
            'formAction' => route('accounts.update', [
                'account' => $account->public_id
            ]),
        ]);
    }

    public function edit(string $id)
    {
        //
    }

    public function update(UpdateAccountRequest $request, User $account)
    {
        $account->first_name = $request->first_name;
        $account->middle_name = $request->middle_name;
        $account->last_name = $request->last_name;
        $account->suffix_name = $request->suffix_name;
        $account->save();
        return redirect()->route('accounts.show', [
            'account' => $account->public_id
        ])->with('status', 'Account updated.');
    }

    public function confirmDestroy(User $account)
    {
        return view('accounts.delete', [
            'backRoute' => route('accounts.show', [
                'account' => $account->public_id
            ]),
            'formAction' => route('accounts.destroy', [
                'account' => $account->public_id
            ]),
        ]);
    }

    public function destroy(User $account)
    {
        $account->delete();
        return redirect()->route('accounts.index')
            ->with('status', 'Account deleted.');
    }

    public function signupInviteIndex()
    {
        return view('accounts.signup-invite-index', [
            'backRoute' => route('accounts.index'),
            'sendAction' => route('accounts.signup-invites.store'),
            'createRoute' => route('accounts.signup-invites.create'),
            'invites' => SignupInvitation::where('is_accepted', 0)->get(),
            'createFormAction' => route('accounts.signup-invites.store'),
            'positions' => Position::open()->get(),
        ]);
    }

    public function createSignupInvite()
    {
        return view('accounts.create-signup-invite', [
            'backRoute' => route('accounts.signup-invites.index'),
            'formAction' => route('accounts.signup-invites.store'),
            'positions' => Position::open()->get(),
        ]);
    }

    public function sendSignupInvite(StoreSignupInvitationRequest $request)
    {
        $signupInvite = new SignupInvitation();
        $signupInvite->invite_code = Str::random(32);
        $signupInvite->email = $request->email;
        $signupInvite->position()->associate(Position::find($request
            ->position));
        $signupInvite->is_accepted = false;
        $signupInvite->expires_at = now()->hour(24)->toDateTimeString();
        $signupInvite->save();
        $url = url('http://' . config('app.user_domain') .
            (str_starts_with(config('app.user_domain'), '127.') ? ':8000'
                : null) .
            route('user.invitation', [
                'invite_code' => $signupInvite->invite_code
            ], false));
        SendSignupInvite::dispatch($signupInvite, $url);
        return redirect()->route('accounts.signup-invites.index')
            ->with('status', 'Sign up invitation sent.');
    }

    public function revokeSignupInvite(SignupInvitation $invite)
    {
        $invite->delete();
        return redirect()->route('accounts.signup-invites.index')
            ->with('status', 'Sign up invitation revoked.');
    }

    public function confirmRevokeSignupInvite(SignupInvitation $invite)
    {
        return view('accounts.revoke-signup-invite', [
            'backRoute' => route('accounts.signup-invites.index'),
            'formAction' => route('accounts.signup-invites.destroy', [
                'invite' => $invite->id
            ]),
			'invite' => $invite,
        ]);
    }

    public function streamSignupInvite()
    {
        return Stream::event('signup_invite');
    }
}
