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

    protected function prepareForValidation(): void
    {
        if ($this->input('city_id') === '' || $this->input('city_id') === null) {
            $this->merge(['city_id' => null]);
        }

        if ($this->input('service_city_ids') === null) {
            $this->merge(['service_city_ids' => []]);
        }
    }

    public function rules(): array
    {
        $partnerId = $this->route('partner')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('partners', 'phone')->ignore($partnerId)],
            'email' => ['nullable', 'email', Rule::unique('partners', 'email')->ignore($partnerId)],
            'city_id' => ['nullable', 'exists:cities,id'],
            'service_city_ids' => ['nullable', 'array'],
            'service_city_ids.*' => ['integer', 'exists:cities,id'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
