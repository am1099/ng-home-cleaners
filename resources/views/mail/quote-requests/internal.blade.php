@component('mail::message')
# New estimate request {{ $quoteRequest->reference }}

**Source:** {{ $quoteRequest->source->label() }}

## Customer

- **Name:** {{ $quoteRequest->fullName() }}
- **Phone:** {{ $quoteRequest->phone }}
- **Email:** {{ $quoteRequest->email }}
- **Address:** {{ $quoteRequest->address_line1 }}, {{ $quoteRequest->postcode }}

## Service

- **Service:** {{ $quoteRequest->service?->name }}
@if ($quoteRequest->frequency)
- **Frequency:** {{ \App\Enums\CleaningFrequency::tryFrom($quoteRequest->frequency)?->label() }}
@endif

## Property

- **Type:** {{ \App\Enums\PropertyType::tryFrom($quoteRequest->property_type)?->label() }}
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

@if (! empty($quoteRequest->pricing_snapshot['line_items']))
## Extras & adjustments

@foreach ($quoteRequest->pricing_snapshot['line_items'] as $item)
- {{ $item['label'] ?? 'Item' }}
@endforeach
@endif

## Visit preference

- **Date:** {{ $quoteRequest->preferred_date->format('l j F Y') }}
- **Arrival:** {{ \App\Enums\ArrivalWindow::tryFrom($quoteRequest->arrival_window)?->label() }}

## Guide estimate

**{{ $quoteRequest->guide_estimate_headline }}**

@component('mail::button', ['url' => route('filament.admin.resources.quote-requests.view', ['record' => $quoteRequest])])
View lead in admin
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
