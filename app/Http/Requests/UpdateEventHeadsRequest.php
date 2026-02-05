<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Exists;
use App\Models\User;

class UpdateEventHeadsRequest extends FormRequest
{
    protected $errorBag = 'event-heads_edit';

    public function attributes(): array
    {
        return [
            'event_heads' => 'event heads',
        ];
    }

    public function rules(): array
    {
        return [
            'event_heads' => ['array'],
            'event_heads.*' => ['nullable', 'numeric', 'integer',
                new Exists(User::has('position')
                    ->notOfPosition(['adviser']), 'public_id', ['0'])
            ],
        ];
    }
}
