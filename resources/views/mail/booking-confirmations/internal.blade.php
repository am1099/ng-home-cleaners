@component('mail::message')
# Booking confirmation received

**{{ $confirmation->full_name }}** has sent booking details from the public confirmation page.

- **Address:** {{ $confirmation->address }}
- **Access:** {{ $confirmation->access_instructions }}
- **Parking:** {{ $confirmation->parking->label() }}
- **Hoover and mop:** {{ $confirmation->equipment_preference->label() }}
@if ($confirmation->notes)
- **Notes:** {{ $confirmation->notes }}
@endif
- **Valuables secured:** yes
- **Payment sent as agreed:** yes

@component('mail::button', ['url' => route('filament.admin.resources.booking-confirmations.view', ['record' => $confirmation])])
View in admin
@endcomponent
@endcomponent
