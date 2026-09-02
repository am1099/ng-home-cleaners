<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

final class Media
{
    /**
     * Disk used for public CRM / marketing media (local "public" or Cloud object storage).
     *
     * On Laravel Cloud the default filesystem disk is injected at runtime (often with a
     * bucket-specific name via LARAVEL_CLOUD_DISK_CONFIG). Prefer that over the static
     * "s3" entry in config/filesystems.php.
     */
    public static function diskName(): string
    {
        $configured = config('filesystems.media');

        if (is_string($configured) && filled($configured) && $configured !== 'public') {
            return $configured;
        }

        $defaultDisk = (string) config('filesystems.default', 'local');

        if (self::diskDriver($defaultDisk) === 's3') {
            return $defaultDisk;
        }

        if (filled(config('filesystems.disks.s3.bucket')) && self::diskDriver('s3') === 's3') {
            return 's3';
        }

        foreach (array_keys(config('filesystems.disks', [])) as $disk) {
            if (in_array($disk, ['local', 'public'], true)) {
                continue;
            }

            if (self::diskDriver($disk) === 's3' && filled(config("filesystems.disks.{$disk}.bucket"))) {
                return $disk;
            }
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

    private static function diskDriver(string $disk): ?string
    {
        $driver = config("filesystems.disks.{$disk}.driver");

        return is_string($driver) ? $driver : null;
    }
}
