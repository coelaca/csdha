<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Exists;
use App\Models\User;

class StoreTaskAssigneeRequest extends FormRequest
{
    protected $errorBag = "task-assignee_create";

    public function rules(): array
    {
        return [
            'assignee' => ['required', 'numeric', 'integer',
                new Exists(User::has('position')
                    ->notOfPosition(['adviser']), 'public_id', [])
            ],
            'role' => ['max:50']
        ];
    }
}
