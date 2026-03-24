<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskStatusRequest extends FormRequest
{
    protected $errorBag = 'task-status_create';

    public function rules(): array
    {
        return [
            'status' => ['required', 'max:255', 
                Rule::unique('App\Models\TaskStatus', 'status_name')
                    ->withoutTrashed()]
        ];
    }
}
