<?php

namespace App\Notifications;

use App\Models\QuoteRequest;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewQuoteRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public QuoteRequest $quoteRequest,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $body = collect([
            $this->quoteRequest->displayName(),
            $this->quoteRequest->service?->name,
            $this->quoteRequest->homeSummary() ?: null,
            $this->quoteRequest->guide_estimate_headline,
            $this->quoteRequest->source->label(),
        ])->filter()->implode(' · ');

        return FilamentNotification::make()
            ->title('New estimate request '.$this->quoteRequest->reference)
            ->body($body)
            ->icon('heroicon-o-inbox')
            ->actions([
                Action::make('view')
                    ->label('View lead')
                    ->url(route('filament.admin.resources.quote-requests.view', ['record' => $this->quoteRequest])),
            ])
            ->getDatabaseMessage();
    }
}
