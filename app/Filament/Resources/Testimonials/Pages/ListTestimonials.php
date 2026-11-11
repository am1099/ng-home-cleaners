<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Concerns\HasToggleableRecordLayout;
use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Services\GoogleReviewClient;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use RuntimeException;

class ListTestimonials extends ListRecords
{
    use HasToggleableRecordLayout;

    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        $google = app(GoogleReviewClient::class);
        $actions = [];

        if ($google->isConfigured() && $google->isConnected()) {
            $actions[] = Action::make('syncGoogle')
                ->label('Sync Google reviews')
                ->action(function () use ($google): void {
                    try {
                        $result = $google->sync();
                    } catch (RuntimeException $exception) {
                        Notification::make()
                            ->title('Google reviews')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Reviews synced')
                        ->body($result['created'].' added, '.$result['updated'].' updated, '.$result['skipped'].' skipped.')
                        ->success()
                        ->send();
                });

            $actions[] = Action::make('disconnectGoogle')
                ->label('Disconnect Google')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Reviews already saved stay on the site. Sync stops until you connect again.')
                ->action(function () use ($google): void {
                    $google->disconnect();

                    Notification::make()
                        ->title('Google disconnected')
                        ->success()
                        ->send();
                });
        } elseif ($google->isConfigured()) {
            $actions[] = Action::make('connectGoogle')
                ->label('Connect Google')
                ->url(route('google-reviews.connect'));
        }

        $actions[] = CreateAction::make();

        return $actions;
    }
}
