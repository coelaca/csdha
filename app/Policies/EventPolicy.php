<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Models\AccomReport;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->hasPerm('events.view')
            ? Response::allow() : Response::deny();
    }

    public function view(User $user, Event $event): Response
    {
        $active = $event->gpoa()->active;
        if (!$active) return Response::deny();
        return ($user->hasPerm('events.view'))
            ? Response::allow() : Response::deny();
    }

    public function create(User $user): Response
    {
        if (!self::canEdit($user)) {
            return Response::deny();
        }
        return Response::allow();
    }

    public function update(User $user, Event $event): Response
    {
        $active = $event->gpoa()->active;
        if (!$active) return Response::deny();
        $canEdit = self::canEdit($user, $event);
        $approved = $event->accomReport?->status === 'approved';
        $pending = $event->accomReport?->status === 'pending';
        $eventHead = $event->gpoaActivity->eventHeads()->whereKey($user->id)
            ->exists();
        $editor = $user->hasPerm('accomplishment-reports.view') &&
            $user->hasPerm('accomplishment-reports.edit');
        return ($canEdit || !($approved || $pending) && ($eventHead || $editor))
            ? Response::allow() : Response::deny();
    }

    public function delete(User $user, Event $event): Response
    {
        if (!self::canEdit($user, $event)) {
            return Response::deny();
        }
        return ($event->creator->is($user) ||
            $event->editors->contains($user))
            ? Response::allow() : Response::deny();
    }

    public function restore(User $user, Event $event): bool
    {
        return false;
    }

    public function forceDelete(User $user, Event $event): bool
    {
        return false;
    }

    public function viewAnyAccomReport(User $user): Response
    {
        $withPosition = $user->position_name;
        $hasPerm = $user->hasPerm('accomplishment-reports.view');
        return ($hasPerm && $withPosition)
            ? Response::allow() : Response::deny();
    }

    public function viewAccomReport(User $user, Event $event): Response
    {
        $active = $event->gpoa()->active;
        if (!$active) return Response::deny();
        $position = $user->position_name;
        if (!in_array($position, ['adviser', 'president', null])) {
            $position = 'officers';
        }
        switch ($position) {
        case 'officers':
            $head = $event->gpoaActivity->eventHeads()->whereKey($user->id)->exists();
            $hasPerm = $user->hasPerm('accomplishment-reports.view');
            if ($head || $hasPerm) {
                return Response::allow();
            }
            break;
        case 'president':
            $head = $event->gpoaActivity->eventHeads()->whereKey($user->id)->exists();
            $pending = $event->accomReport?->status === 'pending';
            $currentStep = $event->accomReport?->current_step === 'president';
            $approved = $event->accomReport?->status === 'approved';
            if ($head || (($pending && $currentStep) || $approved)) {
                return Response::allow();
            }
            break;
        case 'adviser':
            $approved = $event->accomReport?->status === 'approved';
            if ($approved) {
                return Response::allow();
            }
            break;
        }
        return Response::deny();
    }

    public function submitAccomReport(User $user, Event $event): Response
    {
        $active = $event->gpoa()->active;
        if (!$active) return Response::deny();
        if (!self::canEdit($user, $event)) {
            return Response::deny();
        }
        if (!self::canChangeStatus($user, $event)) {
            return Response::deny();
        }
        $canView = $user->hasPerm('accomplishment-reports.view');
        $canEdit = $user->hasPerm('accomplishment-reports.edit');
        if (!$canEdit || !$canView) {
            return Response::deny();
        }
	/*
        $updated = $event->accomReport?->file_updated;
        if (!$updated) return Response::deny();
	*/
        $position = $user->position_name;
        if (!in_array($position, ['adviser', 'president', null])) {
            $position = 'officers';
        }
        switch ($position) {
        case 'officers':
            $hasPerm = $user->hasPerm('accomplishment-reports.view');
            //$exists = $event->accomReport; 
            $returned = $event->accomReport?->status === 'returned';
            $draft = $event->accomReport?->status === 'draft';
            $currentStep = $event->accomReport?->current_step === 'officers';
            if ($hasPerm && ($draft || $returned) && $currentStep) {
                return Response::allow();
            }
            break;
        }
        return Response::deny();
    }

    public function returnAccomReport(User $user, Event $event): Response
    {
        $active = $event->gpoa()->active;
        if (!($active && self::canChangeStatus($user, $event))) {
            return Response::deny();
        }
        $position = $user->position_name;
        if (!in_array($position, ['adviser', 'president', null])) {
            $position = 'officers';
        }
        switch ($position) {
        case 'president':
            $pending = $event->accomReport?->status === 'pending';
            $currentStep = $event->accomReport?->current_step === 'president';
            if ($pending && $currentStep) {
                return Response::allow();
            }
            break;
        }
        return Response::deny();
    }

    public function approveAccomReport(User $user, Event $event): Response
    {
        $active = $event->gpoa()->active;
        if (!($active && self::canChangeStatus($user, $event))) {
            return Response::deny();
        }
        $position = $user->position_name;
        if (!in_array($position, ['adviser', 'president', null])) {
            $position = 'officers';
        }
        switch ($position) {
        case 'president':
            $pending = $event->accomReport?->status === 'pending';
            $currentStep = $event->accomReport?->current_step === 'president';
            if ($pending && $currentStep) {
                return Response::allow();
            }
            break;
        }
        return Response::deny();
    }

    public function makeAccomReport(User $user, Event $event): Response
    {
        $accomReport = $event->accomReport;
        $noFile = !$accomReport?->filepath;
        $outdated = !$accomReport?->file_updated;
        $officersStep = $accomReport?->current_step === 'officers';
        $completed = $event->is_completed;
        $head = $event->gpoaActivity->eventHeads()->whereKey($user->id)
            ->exists();
        $canView = $user->hasPerm('accomplishment-reports.view');
        $canEdit = $user->hasPerm('accomplishment-reports.edit');
        $hasPerm = $canView && $canEdit;
        $approved = $accomReport?->status === 'approved';
        return ($noFile || $outdated && 
            ($head || $hasPerm || $approved)) 
            ? Response::allow() : Response::deny();
    }

    public function genAccomReport(User $user): Response
    {
        $hasPerm = $user->hasPerm('accomplishment-reports.view');
        if ($hasPerm) {
            return Response::allow();
        }
        return Response::deny();
    }

    public function updateAccomReportBG(User $user): Response
    {
        $canView = $user->hasPerm('accomplishment-reports.view');
        $canEdit = $user->hasPerm('accomplishment-reports.edit');
        $hasPerm = $canView && $canEdit;
        $hasPending = AccomReport::active()->where('status', 'pending')
            ->exists();
        return (!$hasPending && $hasPerm) 
            ? Response::allow() : Response::deny();
    }

    public function register(?User $user, Event $event): Response
    {
        $active = $event->gpoa()->active;
        if (!$active) return Response::deny();
        $openRegis = $event->automatic_attendance;
        return ($openRegis) ? Response::allow() : Response::deny();
    }

    public function evaluate(?User $user, Event $event): Response
    {
        $active = $event->gpoa()->active;
        if (!$active) return Response::deny();
        $openEval = $event->accept_evaluation;
        return ($openEval) ? Response::allow() : Response::deny();
    }

    public function recordAttendance(User $user, Event $event): Response
    {
        $active = $event->gpoa()->active;
        if (!$active) return Response::deny();
        $openEval = $event->participant_type;
        return ($openEval) ? Response::allow() : Response::deny();
    }

    public function addAttendee(User $user, Event $event): Response
    {
        $active = $event->gpoa()->active;
        if (!$active) return Response::deny();
        $recordsAttendance = $event->participant_type !== null;
        $manualAttendance = $event->automatic_attendance === 0;
        $update = $this->update($user, $event)->allowed();
        return ($update && $recordsAttendance && $manualAttendance)
            ? Response::allow() : Response::deny();
    }

    public function viewAttendance(User $user): Response
    {
        $canView = $user->hasPerm('attendance.view');
        $canEdit = $user->hasPerm('attendance.edit');
        if (!($canView && $canEdit)) {
            return Response::deny();
        }
        return Response::allow();
    }

    public function updateEventHeads(User $user, Event $event): Response
    {
        $active = $event->gpoa()->active;
        if (!$active) return Response::deny();
        $activity = $event->gpoaActivity;
        $eventHead = $activity->eventHeadsOnly()?->whereKey($user->id)
            ->exists();
        $president = $user->position_name === 'president';
        $adviser = $user->position_name === 'adviser';
        $canEdit = self::canEdit($user, $event);
        return ($eventHead || $canEdit)
            ? Response::allow() : Response::deny();
    }

    private static function canEdit(User $user, ?Event $event = null): bool
    {
        $canView = $user->hasPerm('events.view');
        $canEdit = $user->hasPerm('events.edit');
        $approved = $event?->accomReport?->status === 'approved';
        return ($canView && $canEdit && !($approved ?? false));
    }

    private static function canChangeStatus(User $user, ?Event
        $event = null): bool
    {
        $approved = $event?->accomReport?->status === 'approved';
        return (!($approved ?? false));
    }
}
