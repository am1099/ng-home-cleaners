<?php

namespace App\Filament\Support;

use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class SecureImageUpload
{
    /**
     * Secure public image upload for CRM media.
     *
     * Caps file size, restricts MIME types, and stores under a random filename.
     * Downscaling runs after the file is written to disk (not against the Livewire
     * temp stream) to avoid TemporaryUploadedFile stream failures on Windows.
     */
    public static function make(string $name, string $directory, int $maxWidth = 2000): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->disk('public')
            ->directory($directory)
            ->visibility('public')
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->maxSize(5120)
            ->fetchFileInformation(false)
            ->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file) use ($directory, $maxWidth): string {
                $filename = Str::uuid()->toString().'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $path = $file->storeAs(trim($directory, '/'), $filename, [
                    'disk' => 'public',
                ]);

                if (is_string($path) && $path !== '') {
                    self::downscaleStoredImage($path, $maxWidth);
                }

                return is_string($path) ? $path : trim($directory, '/').'/'.$filename;
            });
    }

    private static function downscaleStoredImage(string $path, int $maxWidth): void
    {
        if (! function_exists('imagecreatefromstring') || $maxWidth < 1) {
            return;
        }

        $absolute = storage_path('app/public/'.$path);

        if (! is_file($absolute)) {
            return;
        }

        $binary = @file_get_contents($absolute);

        if ($binary === false) {
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

        $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

        match ($extension) {
            'png' => imagepng($resized, $absolute),
            'gif' => imagegif($resized, $absolute),
            'webp' => function_exists('imagewebp') ? imagewebp($resized, $absolute, 85) : imagejpeg($resized, $absolute, 85),
            default => imagejpeg($resized, $absolute, 85),
        };

        imagedestroy($image);
        imagedestroy($resized);
    }
}
