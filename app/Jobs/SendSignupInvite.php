<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\SignupInvitation;
use App\Mail\SignupInvitation as SignupInvitationMail;
use Illuminate\Support\Facades\Mail;
use Throwable;
use App\Events\SignupInviteStatusChanged;

class SendSignupInvite implements ShouldQueue
{
    use Queueable;

    public function __construct(public SignupInvitation $signupInvite,
            public string $url)
    {

    }

    public function handle(): void
    {
        $signupInvite = $this->signupInvite;
        Mail::to($signupInvite->email)->send(new SignupInvitationMail(
            $this->url));
        $signupInvite->email_sent = true;
        $signupInvite->save();
        SignupInviteStatusChanged::dispatch($signupInvite);
    }

    public function failed(?Throwable $exception): void
    {
        $signupInvite = $this->signupInvite;
        $signupInvite->email_sent = false;
        $signupInvite->save();
        SignupInviteStatusChanged::dispatch($signupInvite);
    }
}
