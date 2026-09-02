<?php

namespace Tests\Feature;

use App\Support\CloudStorage;
use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class MediaDiskTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_disk_uses_default_s3_disk_on_cloud(): void
    {
        Config::set('filesystems.media', null);
        Config::set('filesystems.default', 'private');
        Config::set('filesystems.disks.private', [
            'driver' => 's3',
            'bucket' => 'fls-test-bucket',
            'key' => 'cloud-key',
            'secret' => 'cloud-secret',
            'region' => 'auto',
            'endpoint' => 'https://cloud-endpoint.test',
            'url' => 'https://cdn.test',
        ]);

        $this->assertSame('private', Media::diskName());
    }

    public function test_media_disk_ignores_s3_config_without_credentials(): void
    {
        Config::set('filesystems.media', 's3');
        Config::set('filesystems.default', 'private');
        Config::set('filesystems.disks.s3', [
            'driver' => 's3',
            'bucket' => 'fls-test-bucket',
            'key' => null,
            'secret' => null,
        ]);
        Config::set('filesystems.disks.private', [
            'driver' => 's3',
            'bucket' => 'fls-test-bucket',
            'key' => 'cloud-key',
            'secret' => 'cloud-secret',
            'endpoint' => 'https://cloud-endpoint.test',
            'url' => 'https://cdn.test',
        ]);

        $this->assertSame('private', Media::diskName());
    }

    public function test_media_disk_honours_explicit_media_disk_override(): void
    {
        Config::set('filesystems.media', 'legacy');
        Config::set('filesystems.default', 'private');
        Config::set('filesystems.disks.legacy', [
            'driver' => 's3',
            'bucket' => 'legacy-bucket',
            'key' => 'legacy-key',
            'secret' => 'legacy-secret',
        ]);

        $this->assertSame('legacy', Media::diskName());
    }

    public function test_media_disk_falls_back_to_public_locally(): void
    {
        Config::set('filesystems.media', null);
        Config::set('filesystems.default', 'local');

        $this->assertSame('public', Media::diskName());
    }

    public function test_laravel_cloud_disk_config_registers_private_disk(): void
    {
        $_SERVER['LARAVEL_CLOUD_DISK_CONFIG'] = json_encode([[
            'disk' => 'private',
            'is_default' => true,
            'access_key_id' => 'cloud-key',
            'access_key_secret' => 'cloud-secret',
            'bucket' => 'fls-test-bucket',
            'url' => 'https://cdn.test',
            'endpoint' => 'https://cloud-endpoint.test',
        ]]);

        CloudStorage::configureDisks($this->app);

        $this->assertSame('private', config('filesystems.default'));
        $this->assertSame('cloud-key', config('filesystems.disks.private.key'));
        $this->assertSame('private', Media::diskName());

        unset($_SERVER['LARAVEL_CLOUD_DISK_CONFIG']);
    }
}
