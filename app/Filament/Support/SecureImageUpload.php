<?php

namespace App\Filament\Support;

use App\Support\Media;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class SecureImageUpload
{
    /**
     * Secure public image upload for CRM media.
     *
     * Caps file size, restricts MIME types, and stores under a random filename.
     * Downscaling runs after the file is written to the media disk (local public
     * or Laravel Cloud Object Storage / S3) — not against the Livewire temp stream.
     */
    public static function make(string $name, string $directory, int $maxWidth = 2000): FileUpload
    {
        $disk = Media::diskName();

        return FileUpload::make($name)
            ->image()
            ->disk($disk)
            ->directory($directory)
            ->visibility('public')
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->maxSize(5120)
            ->fetchFileInformation(false)
            ->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file) use ($directory, $maxWidth, $disk): string {
                $filename = Str::uuid()->toString().'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $path = $file->storeAs(trim($directory, '/'), $filename, [
                    'disk' => $disk,
                ]);

                if (is_string($path) && $path !== '') {
                    self::downscaleStoredImage($disk, $path, $maxWidth);
                }

                return is_string($path) ? $path : trim($directory, '/').'/'.$filename;
            });
    }

    private static function downscaleStoredImage(string $disk, string $path, int $maxWidth): void
    {
        if (! function_exists('imagecreatefromstring') || $maxWidth < 1) {
            return;
        }

        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            return;
        }

        $binary = $storage->get($path);

        if (! is_string($binary) || $binary === '') {
            return;
        }

        $image = @imagecreatefromstring($binary);

        if ($image === false) {
            return;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= $maxWidth && $height <= $maxWidth) {
            imagedestroy($image);

            return;
        }

        $scale = min($maxWidth / max(1, $width), $maxWidth / max(1, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));
        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if ($resized === false) {
            imagedestroy($image);

            return;
        }

        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $temp = tempnam(sys_get_temp_dir(), 'ngimg');

        if ($temp === false) {
            imagedestroy($image);
            imagedestroy($resized);

            return;
        }

        $written = match ($extension) {
            'png' => imagepng($resized, $temp),
            'gif' => imagegif($resized, $temp),
            'webp' => function_exists('imagewebp') ? imagewebp($resized, $temp, 85) : imagejpeg($resized, $temp, 85),
            default => imagejpeg($resized, $temp, 85),
        };

        imagedestroy($image);
        imagedestroy($resized);

        if ($written === false) {
            @unlink($temp);

            return;
        }

        $resizedBinary = file_get_contents($temp);
        @unlink($temp);

        if ($resizedBinary === false) {
            return;
        }

        $storage->put($path, $resizedBinary, 'public');
    }
}
