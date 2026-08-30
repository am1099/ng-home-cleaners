<?php

namespace App\Filament\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceArea;
use App\Services\RevenueCalculator;
use App\Support\UkPostcode;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class BookingCalendar extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|\UnitEnum|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Calendar';

    protected static ?string $slug = 'booking-calendar';

    protected static ?string $title = 'Booking calendar';

    protected string $view = 'filament.pages.booking-calendar';

    #[Url]
    public ?string $month = null;

    #[Url]
    public ?int $serviceId = null;

    #[Url]
    public ?string $postcode = null;

    public ?int $previewBookingId = null;

    public int $selectedMonth = 1;

    public int $selectedYear = 2026;

    public function mount(): void
    {
        $this->month ??= now()->format('Y-m');
        $carbon = $this->monthCarbon();
        $this->selectedMonth = (int) $carbon->month;
        $this->selectedYear = (int) $carbon->year;
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'sm' => 2,
                    'lg' => 4,
                ])
                    ->schema([
                        Select::make('selectedMonth')
                            ->label('Month')
                            ->options(fn (): array => $this->monthOptions())
                            ->required()
                            ->native(false)
                            ->live(),
                        Select::make('selectedYear')
                            ->label('Year')
                            ->options(fn (): array => $this->yearOptions())
                            ->required()
                            ->native(false)
                            ->live(),
                        Select::make('serviceId')
                            ->label('Service')
                            ->placeholder('All services')
                            ->options(fn (): array => $this->filterServices()->pluck('name', 'id')->all())
                            ->searchable()
                            ->nullable()
                            ->native(false)
                            ->live(),
                        Select::make('postcode')
                            ->label('Postcode district')
                            ->placeholder('All districts')
                            ->options(fn (): array => collect($this->filterPostcodes())->mapWithKeys(
                                fn (string $district): array => [$district => $district]
                            )->all())
                            ->searchable()
                            ->nullable()
                            ->native(false)
                            ->live(),
                    ]),
            ])
            // Bind directly to page public properties (not a nested data bag).
            ->statePath(null);
    }

    public function previousMonth(): void
    {
        $this->applyMonth($this->monthCarbon()->subMonth());
    }

    public function nextMonth(): void
    {
        $this->applyMonth($this->monthCarbon()->addMonth());
    }

    public function goToToday(): void
    {
        $this->applyMonth(now());
    }

    public function updatedSelectedMonth(int|string $value): void
    {
        $this->applyMonth(Carbon::create($this->selectedYear, (int) $value, 1));
    }

    public function updatedSelectedYear(int|string $value): void
    {
        $this->applyMonth(Carbon::create((int) $value, $this->selectedMonth, 1));
    }

    public function updatedServiceId(): void
    {
        $this->previewBookingId = null;
    }

    public function updatedPostcode(): void
    {
        $this->previewBookingId = null;
    }

    public function clearFilters(): void
    {
        $this->serviceId = null;
        $this->postcode = null;
        $this->previewBookingId = null;
    }

    public function openBooking(int $bookingId): void
    {
        $this->previewBookingId = $bookingId;
    }

    public function closeBookingPreview(): void
    {
        $this->previewBookingId = null;
    }

    public function monthCarbon(): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m', (string) $this->month)->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }

    public function monthLabel(): string
    {
        return $this->monthCarbon()->format('F Y');
    }

    /**
     * @return array<int, string>
     */
    public function monthOptions(): array
    {
        $options = [];

        for ($month = 1; $month <= 12; $month++) {
            $options[$month] = Carbon::create(null, $month, 1)->format('F');
        }

        return $options;
    }

    /**
     * @return array<int, int>
     */
    public function yearOptions(): array
    {
        $current = (int) now()->year;
        $options = [];

        for ($year = $current - 2; $year <= $current + 3; $year++) {
            $options[$year] = $year;
        }

        return $options;
    }

    /**
     * @return Collection<int, Service>
     */
    public function filterServices(): Collection
    {
        return Service::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']);
    }

    /**
     * @return list<string>
     */
    public function filterPostcodes(): array
    {
        $fromAreas = ServiceArea::query()
            ->orderBy('postcode_label')
            ->pluck('postcode_label')
            ->filter()
            ->map(fn ($label) => strtoupper(trim((string) $label)))
            ->all();

        $fromBookings = Booking::query()
            ->whereNotNull('postcode')
            ->pluck('postcode')
            ->map(fn ($postcode) => UkPostcode::district((string) $postcode))
            ->filter()
            ->all();

        return collect($fromAreas)
            ->merge($fromBookings)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return Collection<string, Collection<int, Booking>>
     */
    public function bookingsByDate(): Collection
    {
        $start = $this->monthCarbon()->copy()->startOfMonth();
        $end = $this->monthCarbon()->copy()->endOfMonth();

        $query = Booking::query()
            ->with(['customer', 'service'])
            ->whereDate('booking_date', '>=', $start->toDateString())
            ->whereDate('booking_date', '<=', $end->toDateString())
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->orderBy('booking_date')
            ->orderBy('arrival_window');

        if ($this->serviceId) {
            $query->where('service_id', $this->serviceId);
        }

        $bookings = $query->get();

        if (filled($this->postcode)) {
            $district = strtoupper(trim((string) $this->postcode));
            $bookings = $bookings->filter(
                fn (Booking $booking): bool => UkPostcode::district($booking->postcode) === $district
            );
        }

        return $bookings->groupBy(fn (Booking $booking): string => $booking->booking_date->toDateString());
    }

    /**
     * @return list<array{date: Carbon, inMonth: bool, bookings: Collection<int, Booking>}>
     */
    public function calendarDays(): array
    {
        $month = $this->monthCarbon();
        $start = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $byDate = $this->bookingsByDate();

        $days = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $days[] = [
                'date' => $cursor->copy(),
                'inMonth' => $cursor->month === $month->month,
                'bookings' => $byDate->get($key, collect()),
            ];
            $cursor->addDay();
        }

        return $days;
    }

    public function previewBooking(): ?Booking
    {
        if (! $this->previewBookingId) {
            return null;
        }

        return Booking::query()
            ->with(['customer', 'service', 'payments'])
            ->find($this->previewBookingId);
    }

    public function bookingUrl(Booking $booking): string
    {
        return BookingResource::getUrl('view', ['record' => $booking]);
    }

    public function editBookingUrl(Booking $booking): string
    {
        return BookingResource::getUrl('edit', ['record' => $booking]);
    }

    public function monthRevenue(): string
    {
        $start = $this->monthCarbon()->copy()->startOfMonth();
        $end = $this->monthCarbon()->copy()->endOfMonth();

        return app(RevenueCalculator::class)->totalFormatted($start, $end);
    }

    public function postcodePill(Booking $booking): string
    {
        return UkPostcode::district($booking->postcode) ?? '—';
    }

    private function applyMonth(Carbon $date): void
    {
        $this->month = $date->format('Y-m');
        $this->selectedMonth = (int) $date->month;
        $this->selectedYear = (int) $date->year;
        $this->previewBookingId = null;
    }
}
