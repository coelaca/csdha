<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskFileRequest extends FormRequest
{
    protected $errorBag = "task-file_create";

    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255'],
            'url' => ['required', 'url:http,https', 'max:2000'],
        ];
    }
}
