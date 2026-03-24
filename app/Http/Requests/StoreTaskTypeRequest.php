<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskTypeRequest extends FormRequest
{
    protected $errorBag = 'task-type_create';

    public function rules(): array
    {
        return [
            'type' => ['required', 'max:255', 
                Rule::unique('App\Models\TaskType', 'type_name')
                    ->withoutTrashed()]
        ];
    }
}
