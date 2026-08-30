<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

final class Media
{
    /**
     * Disk used for public CRM / marketing media (local "public" or Cloud S3).
     */
    public static function diskName(): string
    {
        return (string) config('filesystems.media', 'public');
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(self::diskName());
    }

    public static function url(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        return self::disk()->url($path);
    }
}
