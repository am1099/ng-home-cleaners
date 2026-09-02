<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

final class Media
{
    /**
     * Disk used for public CRM / marketing media (local "public" or Cloud object storage).
     *
     * On Laravel Cloud the bucket credentials live on the injected default disk (often
     * named "private" via LARAVEL_CLOUD_DISK_CONFIG), not the static "s3" config entry.
     */
    public static function diskName(): string
    {
        $configured = config('filesystems.media');

        if (is_string($configured) && filled($configured) && self::diskIsUsable($configured)) {
            return $configured;
        }

        $defaultDisk = (string) config('filesystems.default', 'local');

        if (self::diskIsUsable($defaultDisk)) {
            return $defaultDisk;
        }

        foreach (array_keys(config('filesystems.disks', [])) as $disk) {
            if (self::diskDriver($disk) === 's3' && self::diskIsUsable($disk)) {
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

    public static function diskIsUsable(string $disk): bool
    {
        $driver = self::diskDriver($disk);

        if ($driver === null) {
            return false;
        }

        if ($driver === 'local') {
            return $disk === 'public';
        }

        if ($driver !== 's3') {
            return false;
        }

        return filled(config("filesystems.disks.{$disk}.key"))
            && filled(config("filesystems.disks.{$disk}.secret"))
            && filled(config("filesystems.disks.{$disk}.bucket"));
    }

    private static function diskDriver(string $disk): ?string
    {
        $driver = config("filesystems.disks.{$disk}.driver");

        return is_string($driver) ? $driver : null;
    }
}
