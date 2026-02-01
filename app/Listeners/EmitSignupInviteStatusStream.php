<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\SignupInviteStatusChanged;
use App\Services\Stream;

class EmitSignupInviteStatusStream
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SignupInviteStatusChanged $event): void
    {
        $invite = $event->invite;
        $emailSent = $invite->email_sent;
        $cache = 'signup_invite';
        $event = 'signupInviteStatusChanged';
        $data = [
            'id' => $invite->id,
            'emailSent' => $emailSent
        ];
        Stream::store($cache, $event, $data);
    }
}
