<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $partnerId = $this->route('partner')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('partners', 'phone')->ignore($partnerId)],
            'email' => ['nullable', 'email', Rule::unique('partners', 'email')->ignore($partnerId)],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
