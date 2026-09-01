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
        $configured = (string) config('filesystems.media', 'public');

        if ($configured !== 'public') {
            return $configured;
        }

        $defaultDisk = (string) config('filesystems.default', 'local');
        $bucket = config('filesystems.disks.s3.bucket');

        if (filled($bucket) || $defaultDisk === 's3') {
            return 's3';
        }

        return 'public';
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
