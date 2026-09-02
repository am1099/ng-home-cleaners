@component('mail::message')
# {{ $heading }}

{!! $bodyHtml !!}

## Visit preference

- **Date:** {{ $quoteRequest->preferred_date?->format('l j F Y') ?? '—' }}
- **Arrival:** {{ \App\Enums\ArrivalWindow::tryFrom((string) $quoteRequest->arrival_window)?->label() ?? '—' }}

@if ($quoteRequest->frequency)
- **Frequency:** {{ \App\Enums\CleaningFrequency::tryFrom($quoteRequest->frequency)?->label() }}
@endif

## Property

- **Type:** {{ \App\Enums\PropertyType::tryFrom((string) $quoteRequest->property_type)?->label() ?? '—' }}
- **Bedrooms:** {{ $quoteRequest->bedrooms === 0 ? 'Studio' : $quoteRequest->bedrooms }}
- **Floors:** {{ $quoteRequest->floors }}

@if ($quoteRequest->bathrooms !== null)
## Rooms

- **Bathrooms:** {{ $quoteRequest->bathrooms }}
- **WC:** {{ $quoteRequest->wcs }}
- **Kitchens:** {{ $quoteRequest->kitchens }}
- **Reception rooms:** {{ $quoteRequest->reception_rooms }}
@endif

## Condition

@if ($quoteRequest->property_status)
- **Furnishing:** {{ \App\Enums\PropertyStatus::tryFrom($quoteRequest->property_status)?->label() }}
@endif
@if ($quoteRequest->condition_flags)
- **Flags:** {{ collect($quoteRequest->condition_flags)->map(fn ($f) => \App\Enums\ConditionFlag::tryFrom($f)?->label())->filter()->join(', ') }}
@endif
@if ($quoteRequest->condition_notes)
- **Notes:** {{ $quoteRequest->condition_notes }}
@endif

@if (! empty($quoteRequest->property_photo_paths))
## Property photos

{{ count($quoteRequest->property_photo_paths) }} photo{{ count($quoteRequest->property_photo_paths) === 1 ? '' : 's' }} saved with this request. Open the lead in admin to view them.
@endif

@component('mail::button', ['url' => route('filament.admin.resources.quote-requests.view', ['record' => $quoteRequest])])
View lead in admin
@endcomponent
@endcomponent
