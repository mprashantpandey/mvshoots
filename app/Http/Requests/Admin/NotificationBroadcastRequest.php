<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin;
use App\Models\Owner;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationBroadcastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_type' => ['required', Rule::in(['user', 'partner', 'owner', 'admin'])],
            'user_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $model = match ($this->input('user_type')) {
                        'user' => User::class,
                        'partner' => Partner::class,
                        'owner' => Owner::class,
                        'admin' => Admin::class,
                        default => null,
                    };

                    if (! $model || ! $model::query()->whereKey($value)->exists()) {
                        $fail('The selected target user is invalid.');
                    }
                },
            ],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'type' => ['required', 'string', 'max:100'],
            'reference_id' => ['nullable', 'integer'],
        ];
    }
}
