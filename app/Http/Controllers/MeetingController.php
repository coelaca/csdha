<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\MeetingStatus;
use App\Http\Requests\SaveMeetingRequest;

class MeetingController extends Controller
{
    public function index()
    {
        return view('meetings.index', [
            'meetings' => $meetings,
        ]);
    }

    public function create()
    {
        return view('meetings.create', [

        ]);
    }

    public function show(Meeting $meeting)
    {
        return view('meetings.create', [
            'meeting' => $meeting,
        ]);
    }

    public function confirmDestroy(Meeting $meeting)
    {
        return view('meetings.delete', [
            'meeting' => $meeting,
        ]);
    }

    public function store(SaveMeetingRequest $request)
    {
        self::storeOrUpdate($request);
        return redirect()->route('meeting.index');
    }

    public function update(SaveMeetingRequest $request, Meeting $meeting)
    {
        self::storeOrUpdate($request, $meeting);
        return redirect()->route('meeting.index');
    }

    public function destroy(Meeting $meeting)
    {
        $meeting->destroy();
        return redirect()->route('meeting.index');
    }

    private static function storeOrUpdate(Request $request, 
        Meeting $meeting = null)
    {
        if (!$meeting) {
            $meeting = new Meeting;
        }
        $meeting->title = $request->title;
        $meeting->schedule = $request->schedule;
        $meeting->location = $request->location;
        $meeting->agenda = $request->agenda;
        $meeting->minutes = $request->minutes;
        $meeting->save();
        if ($request->has('status')) {
            $meetingStatus = MeetingStatus::find($request->type);
            $meeting->status()->associate($meetingStatus);
        }
        $meeting->save();
    }
}
