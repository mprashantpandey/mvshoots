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
            'video_file' => ['nullable', 'file', 'mimes:mp4,mov,avi,webm', 'max:131072'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a reel title.',
            'video_url.url' => 'Enter a valid video URL starting with https://',
            'video_file.mimes' => 'Video file must be MP4, MOV, AVI, or WEBM.',
            'video_file.max' => 'Video file must be 128 MB or smaller.',
            'video_file.uploaded' => 'Video upload failed before it reached the app. This usually means the server upload limit was exceeded. Try a smaller file or increase the server limit.',
            'thumbnail.image' => 'Thumbnail must be an image file.',
            'thumbnail.max' => 'Thumbnail must be 4 MB or smaller.',
            'thumbnail.uploaded' => 'Thumbnail upload failed before it reached the app. This usually means the server upload limit was exceeded.',
            'category_id.exists' => 'Choose a valid category.',
            'status.in' => 'Choose a valid status.',
        ];
    }
}
