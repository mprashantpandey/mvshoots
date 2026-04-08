<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateCityAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('admin') ?? $this->user();

        return $user instanceof Admin && $user->isMainAdmin();
    }

    public function rules(): array
    {
        $staffId = $this->route('admin')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($staffId)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'city_id' => ['required', 'exists:cities,id'],
        ];
    }
}
