<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ownerId = $this->route('owner')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('owners', 'email')->ignore($ownerId)],
            'password' => [$ownerId ? 'nullable' : 'required', 'string', 'min:6'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
