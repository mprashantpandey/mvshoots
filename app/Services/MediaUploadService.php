<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class MediaUploadService
{
    public function upload(?UploadedFile $file, string $directory): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store($directory, 'public');
    }
}
