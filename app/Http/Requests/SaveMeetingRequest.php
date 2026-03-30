<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\MaxText;

class SaveMeetingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'max:255'],
            'schedule' => ['required', 'dateTime'],
            'location' => ['required', 'max:2000'],
            'agenda' => ['required', new MaxText],
            'minutes' => [new MaxText],
            'status' => ['numeric', 'integer', 
                'exists:App\Models\MeetingStatus,id'],
        ];
    }
}
