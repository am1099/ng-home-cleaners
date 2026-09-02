<?php

namespace App\Filament\Resources\QuoteRequests\Schemas;

use App\Enums\ArrivalWindow;
use App\Enums\CleaningFrequency;
use App\Enums\ConditionFlag;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Models\Addon;
use App\Pricing\Money;
use App\Support\ArrayState;
use App\Support\Media;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Lead summary')
                ->columns(3)
                ->schema([
                    TextEntry::make('reference')->copyable()->weight('semibold'),
                    TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn (QuoteRequestStatus $state): string => $state->label())
                        ->color(fn (QuoteRequestStatus $state): string => $state->color()),
                    TextEntry::make('source')
                        ->badge()
                        ->formatStateUsing(fn (QuoteRequestSource $state): string => $state->label())
                        ->color(fn (QuoteRequestSource $state): string => $state->color()),
                    TextEntry::make('submitted_at')->label('Submitted')->dateTime('d M Y H:i'),
                    TextEntry::make('service.name')->label('Service'),
                    TextEntry::make('guide_estimate_headline')->label('Guide estimate')->placeholder('—'),
                    TextEntry::make('final_quote_amount_pence')
                        ->label('Final quoted amount')
                        ->formatStateUsing(fn (?int $state): string => $state !== null ? Money::formatPence($state) : 'Not set')
                        ->weight('semibold'),
                    TextEntry::make('customer.id')
                        ->label('Customer record')
                        ->formatStateUsing(fn ($state, $record) => $record->customer?->fullName())
                        ->url(fn ($record) => $record->customer_id
                            ? route('filament.admin.resources.customers.view', ['record' => $record->customer_id])
                            : null),
                ]),

            Section::make('Customer details')
                ->columns(2)
                ->schema([
                    TextEntry::make('full_name')->label('Name')->state(fn ($record) => $record->fullName()),
                    TextEntry::make('phone')->copyable(),
                    TextEntry::make('email')->copyable()->placeholder('—'),
                    TextEntry::make('postcode')->placeholder('—'),
                    TextEntry::make('address_line1')->label('Address')->placeholder('—')->columnSpanFull(),
                    TextEntry::make('address_line2')->label('Address line 2')->placeholder('—')->columnSpanFull(),
                    TextEntry::make('city')->placeholder('—'),
                    TextEntry::make('parking_notes')->placeholder('—')->columnSpanFull(),
                    TextEntry::make('access_notes')->placeholder('—')->columnSpanFull(),
                ]),

            Section::make('Visit preference')
                ->columns(2)
                ->schema([
                    TextEntry::make('preferred_date')->date('l j F Y')->placeholder('—'),
                    TextEntry::make('arrival_window')->formatStateUsing(
                        fn (?string $state) => ArrivalWindow::tryFrom((string) $state)?->label() ?? '—',
                    ),
                    TextEntry::make('frequency')->formatStateUsing(
                        fn (?string $state) => $state ? (CleaningFrequency::tryFrom($state)?->label() ?? $state) : '—',
                    ),
                ]),

            Section::make('Property & rooms')
                ->columns(3)
                ->schema([
                    TextEntry::make('property_type')->formatStateUsing(
                        fn (?string $state) => PropertyType::tryFrom((string) $state)?->label() ?? '—',
                    ),
                    TextEntry::make('bedrooms')->formatStateUsing(
                        fn (?int $state) => $state === null ? '—' : ($state === 0 ? 'Studio' : (string) $state),
                    ),
                    TextEntry::make('floors')->placeholder('—'),
                    TextEntry::make('split_level_flat')->label('Split-level flat')->formatStateUsing(
                        fn (?bool $state) => $state ? 'Yes' : 'No',
                    ),
                    TextEntry::make('bathrooms')->placeholder('—'),
                    TextEntry::make('wcs')->label('WCs')->placeholder('—'),
                    TextEntry::make('kitchens')->placeholder('—'),
                    TextEntry::make('reception_rooms')->label('Reception rooms')->placeholder('—'),
                    TextEntry::make('extra_rooms')->formatStateUsing(
                        fn (mixed $state) => empty(ArrayState::normalize($state)) ? '—' : collect(ArrayState::normalize($state))->join(', '),
                    )->columnSpanFull(),
                ]),

            Section::make('Condition')
                ->columns(2)
                ->schema([
                    TextEntry::make('property_status')->formatStateUsing(
                        fn (?string $state) => $state ? (PropertyStatus::tryFrom($state)?->label() ?? $state) : '—',
                    ),
                    TextEntry::make('condition_flags')->formatStateUsing(function (mixed $state): string {
                        $flags = ArrayState::normalize($state);

                        if ($flags === []) {
                            return '—';
                        }

                        return collect($flags)
                            ->map(fn ($flag) => ConditionFlag::tryFrom((string) $flag)?->label())
                            ->filter()
                            ->join(', ');
                    })->columnSpanFull(),
                    TextEntry::make('condition_notes')->placeholder('—')->columnSpanFull(),
                ]),

            Section::make('Property photos')
                ->visible(fn ($record): bool => ! empty($record->property_photo_paths))
                ->schema([
                    ImageEntry::make('property_photo_paths')
                        ->label('Uploaded photos')
                        ->state(fn ($record): array => collect($record->property_photo_paths ?? [])
                            ->map(fn (string $path): ?string => Media::url($path))
                            ->filter()
                            ->values()
                            ->all())
                        ->columnSpanFull(),
                    TextEntry::make('photo_count')
                        ->label('Count')
                        ->state(fn ($record): string => (string) count($record->property_photo_paths ?? [])),
                ]),

            Section::make('Extras')
                ->schema([
                    TextEntry::make('addon_ids')
                        ->label('Selected add-ons')
                        ->formatStateUsing(function (mixed $state): string {
                            $ids = ArrayState::normalize($state);

                            if ($ids === []) {
                                return 'None';
                            }

                            return Addon::query()
                                ->whereIn('id', $ids)
                                ->orderBy('sort_order')
                                ->pluck('label')
                                ->join(', ') ?: 'None';
                        }),
                ]),

            Section::make('Submitted estimate breakdown')
                ->schema([
                    TextEntry::make('guide_estimate_detail')->label('Guide detail')->placeholder('—')->columnSpanFull(),
                    TextEntry::make('pricing_breakdown')
                        ->label('Price breakdown')
                        ->state(function ($record): string {
                            $state = $record->pricing_snapshot;

                            if (! is_array($state) || $state === []) {
                                return 'No pricing snapshot stored for this lead.';
                            }

                            if (! empty($state['manual'])) {
                                return 'Manual CRM lead — no automatic pricing snapshot.';
                            }

                            $lines = [];

                            if (isset($state['base_subtotal']['min_pence'], $state['base_subtotal']['max_pence'])) {
                                $lines[] = '**Base band:** '.Money::formatPenceRange(
                                    (int) $state['base_subtotal']['min_pence'],
                                    (int) $state['base_subtotal']['max_pence'],
                                );
                            }

                            foreach ($state['line_items'] ?? [] as $item) {
                                $amount = '';
                                if (isset($item['amount']['min_pence'], $item['amount']['max_pence'])) {
                                    $amount = ' — '.Money::formatPenceRange(
                                        (int) $item['amount']['min_pence'],
                                        (int) $item['amount']['max_pence'],
                                    );
                                }

                                $lines[] = '- **'.($item['label'] ?? 'Item').'**'.$amount
                                    .(filled($item['detail'] ?? null) ? ' ('.$item['detail'].')' : '');
                            }

                            if (isset($state['final_range']['min_pence'], $state['final_range']['max_pence'])) {
                                $lines[] = '**Final range:** '.Money::formatPenceRange(
                                    (int) $state['final_range']['min_pence'],
                                    (int) $state['final_range']['max_pence'],
                                );
                            }

                            if (! empty($state['final_single_pence'])) {
                                $lines[] = '**Single guide price:** '.Money::formatPence((int) $state['final_single_pence']);
                            }

                            return $lines !== [] ? implode("\n\n", $lines) : 'Snapshot present but empty.';
                        })
                        ->markdown()
                        ->columnSpanFull(),
                ]),

            Section::make('Internal CRM')
                ->columns(2)
                ->schema([
                    TextEntry::make('internal_notes')->placeholder('No notes yet.')->columnSpanFull(),
                    TextEntry::make('contacted_at')->dateTime('d M Y H:i')->placeholder('—'),
                    TextEntry::make('quote_sent_at')->dateTime('d M Y H:i')->placeholder('—'),
                    TextEntry::make('won_at')->dateTime('d M Y H:i')->placeholder('—'),
                    TextEntry::make('lost_at')->dateTime('d M Y H:i')->placeholder('—'),
                    TextEntry::make('whatsapp_initiated_at')->dateTime('d M Y H:i')->placeholder('—'),
                ]),
        ]);
    }
}
