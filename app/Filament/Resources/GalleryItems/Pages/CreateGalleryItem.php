<?php

namespace App\Filament\Resources\GalleryItems\Pages;

use App\Filament\Resources\GalleryItems\GalleryItemResource;
use App\Filament\Support\SecureImageUpload;
use App\Models\GalleryItem;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class CreateGalleryItem extends CreateRecord
{
    protected static string $resource = GalleryItemResource::class;

    protected static ?string $title = 'Upload gallery photos';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Upload photos')
                ->description('Drop several images at once. Captions, alt text, and links can be filled in from the gallery list afterwards.')
                ->schema([
                    SecureImageUpload::bulkGallery('images', 'gallery', 1600)
                        ->label('Images')
                        ->multiple()
                        ->required()
                        ->helperText('JPEG, PNG, WebP or GIF. You can select many files in one go.'),
                    Toggle::make('is_published')
                        ->label('Publish immediately')
                        ->default(true)
                        ->inline(false)
                        ->helperText('Turn off to keep uploads as drafts until you add captions.'),
                ]),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $paths = $data['images'] ?? [];

        if (! is_array($paths)) {
            $paths = filled($paths) ? [$paths] : [];
        }

        $paths = array_values(array_filter($paths));

        if ($paths === []) {
            throw new \RuntimeException('Upload at least one image.');
        }

        $baseSort = (int) (GalleryItem::query()->max('sort_order') ?? 0);
        $published = (bool) ($data['is_published'] ?? true);
        $first = null;

        foreach ($paths as $index => $path) {
            $item = GalleryItem::query()->create([
                'image_path' => $path,
                'alt_text' => 'Cleaning photo',
                'caption' => null,
                'sort_order' => $baseSort + $index + 1,
                'is_published' => $published,
                'published_at' => $published ? now() : null,
            ]);

            $first ??= $item;
        }

        $count = count($paths);

        Notification::make()
            ->title($count === 1 ? 'Photo uploaded' : "{$count} photos uploaded")
            ->body('Open each item from the list to add captions and alt text when you are ready.')
            ->success()
            ->send();

        return $first;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }
}
