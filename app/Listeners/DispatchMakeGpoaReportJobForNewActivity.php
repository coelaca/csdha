<?php

namespace App\Listeners;

use App\Events\GpoaActivityStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Jobs\MakeGpoaReport;

class DispatchMakeGpoaReportJobForNewActivity
{
    public function __construct()
    {
        //
    }

    public function handle(GpoaActivityStatusChanged $event): void
    {
/*
        $gpoa = $event->activity->gpoa;
        $activity = $event->activity;
        if ($activity->status !== 'approved') return;
        $gpoa->report_file_updated = false;
        $gpoa->save();
        MakeGpoaReport::dispatch($gpoa, auth()->user(), $activity)
            ->onQueue('pdf');
*/
    }
}
