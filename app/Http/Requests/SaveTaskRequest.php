<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\MaxText;

class SaveTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'task' => ['required', 'max:255'],
            'deadline' => ['required', 'date'],
            'notes' => [new MaxText],
            'type' => ['numeric', 'integer', 'exists:App\Models\TaskType,id'],
            'status' => ['numeric', 'integer', 'exists:App\Models\TaskStatus,id'],
        ];
    }
}
