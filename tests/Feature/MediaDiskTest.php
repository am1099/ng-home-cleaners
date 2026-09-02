<?php

namespace Tests\Feature;

use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class MediaDiskTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_disk_uses_default_s3_disk_on_cloud(): void
    {
        Config::set('filesystems.media', 'public');
        Config::set('filesystems.default', 'ng_home_cleaners_production');
        Config::set('filesystems.disks.ng_home_cleaners_production', [
            'driver' => 's3',
            'bucket' => 'ng_home_cleaners_production',
            'key' => 'cloud-key',
            'secret' => 'cloud-secret',
            'region' => 'auto',
            'endpoint' => 'https://cloud-endpoint.test',
            'url' => 'https://cdn.test',
        ]);

        $this->assertSame('ng_home_cleaners_production', Media::diskName());
    }

    public function test_media_disk_honours_explicit_media_disk_override(): void
    {
        Config::set('filesystems.media', 'legacy');
        Config::set('filesystems.default', 'ng_home_cleaners_production');
        Config::set('filesystems.disks.legacy', [
            'driver' => 's3',
            'bucket' => 'legacy-bucket',
        ]);

        $this->assertSame('legacy', Media::diskName());
    }

    public function test_media_disk_falls_back_to_public_locally(): void
    {
        Config::set('filesystems.media', 'public');
        Config::set('filesystems.default', 'local');

        $this->assertSame('public', Media::diskName());
    }
}
