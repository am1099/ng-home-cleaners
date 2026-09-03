<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

final class InvoiceStorage
{
    /**
     * Private disk for issued invoice PDFs.
     *
     * Prefer INVOICE_DISK when set. Locally use the Laravel "local" disk
     * (storage/app/private). On Cloud, reuse usable object storage (same
     * resolution path as Media) but always write with private visibility.
     */
    public static function diskName(): string
    {
        $configured = config('filesystems.invoice');

        if (is_string($configured) && filled($configured) && self::diskIsUsable($configured)) {
            return $configured;
        }

        $defaultDisk = (string) config('filesystems.default', 'local');

        if ($defaultDisk === 'local' || self::diskDriver($defaultDisk) === 'local') {
            return 'local';
        }

        if (self::diskIsUsable($defaultDisk)) {
            return $defaultDisk;
        }

        $mediaDisk = Media::diskName();

        if ($mediaDisk !== 'public' && self::diskIsUsable($mediaDisk)) {
            return $mediaDisk;
        }

        foreach (array_keys(config('filesystems.disks', [])) as $disk) {
            if (self::diskDriver($disk) === 's3' && self::diskIsUsable($disk)) {
                return $disk;
            }
        }

        return 'local';
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(self::diskName());
    }

    public static function put(string $path, mixed $contents): bool
    {
        return self::disk()->put($path, $contents, 'private');
    }

    public static function get(string $path): ?string
    {
        if (! self::exists($path)) {
            return null;
        }

        $contents = self::disk()->get($path);

        return is_string($contents) ? $contents : null;
    }

    public static function exists(string $path): bool
    {
        return self::disk()->exists($path);
    }

    public static function delete(string $path): bool
    {
        if (! self::exists($path)) {
            return false;
        }

        return self::disk()->delete($path);
    }

    public static function pathForNumber(string $invoiceNumber, ?int $year = null): string
    {
        $year ??= (int) now()->year;

        if (preg_match('/^NG-(\d{4})-/', $invoiceNumber, $matches) === 1) {
            $year = (int) $matches[1];
        }

        return "invoices/{$year}/{$invoiceNumber}.pdf";
    }

    public static function diskIsUsable(string $disk): bool
    {
        $driver = self::diskDriver($disk);

        if ($driver === null) {
            return false;
        }

        if ($driver === 'local') {
            return $disk === 'local';
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
