<?php

namespace App\Support;

use Illuminate\Contracts\Foundation\Application;

final class CloudStorage
{
    /**
     * Register Laravel Cloud object storage disks from LARAVEL_CLOUD_DISK_CONFIG.
     *
     * Cloud injects credentials onto a named disk (commonly "private"), not the
     * static "s3" entry in config/filesystems.php.
     */
    public static function configureDisks(Application $app): void
    {
        $raw = $_SERVER['LARAVEL_CLOUD_DISK_CONFIG']
            ?? $_ENV['LARAVEL_CLOUD_DISK_CONFIG']
            ?? getenv('LARAVEL_CLOUD_DISK_CONFIG');

        if (! is_string($raw) || $raw === '') {
            return;
        }

        $disks = json_decode($raw, true);

        if (! is_array($disks)) {
            return;
        }

        foreach ($disks as $disk) {
            if (! is_array($disk) || ! isset($disk['disk'])) {
                continue;
            }

            $name = (string) $disk['disk'];

            $app['config']->set("filesystems.disks.{$name}", [
                'driver' => 's3',
                'key' => $disk['access_key_id'] ?? null,
                'secret' => $disk['access_key_secret'] ?? null,
                'bucket' => $disk['bucket'] ?? null,
                'url' => $disk['url'] ?? $app['config']->get('filesystems.disks.s3.url'),
                'endpoint' => $disk['endpoint'] ?? $app['config']->get('filesystems.disks.s3.endpoint'),
                'region' => $disk['region'] ?? $app['config']->get('filesystems.disks.s3.region', 'auto'),
                'use_path_style_endpoint' => (bool) ($disk['use_path_style_endpoint']
                    ?? $app['config']->get('filesystems.disks.s3.use_path_style_endpoint', false)),
                'visibility' => 'public',
                'throw' => false,
                'report' => false,
            ]);

            if ($disk['is_default'] ?? false) {
                $app['config']->set('filesystems.default', $name);
            }
        }
    }
}
