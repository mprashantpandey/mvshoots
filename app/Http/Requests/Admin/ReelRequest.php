<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'video_url' => ['nullable', 'url'],
            'video_file' => ['nullable', 'file', 'mimes:mp4,mov,avi,webm', 'max:51200'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
