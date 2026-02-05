<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Exists;
use App\Models\User;

class UpdateEventCoheadsRequest extends FormRequest
{
    protected $errorBag = 'event-coheads_edit';

    public function attributes(): array
    {
        return [
            'coheads' => 'co-heads'
        ];
    }

    public function rules(): array
    {
        return [
            'coheads' => ['array'],
            'coheads.*' => ['nullable', 'numeric', 'integer',
                new Exists(User::has('position')
                    ->notOfPosition(['adviser']), 'public_id', ['0'])
            ]
        ];
    }
}
