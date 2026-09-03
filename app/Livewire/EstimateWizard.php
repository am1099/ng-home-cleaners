<?php

namespace App\Livewire;

use App\Actions\DispatchQuoteRequestNotifications;
use App\Data\WizardSubmissionData;
use App\Enums\AccessOption;
use App\Enums\ArrivalWindow;
use App\Enums\CleaningFrequency;
use App\Enums\ConditionFlag;
use App\Enums\ExtraRoomType;
use App\Enums\ParkingOption;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\QuoteRequestSource;
use App\Models\Addon;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Pricing\AddonPriceFormatter;
use App\Pricing\Data\CalculationResult;
use App\Pricing\Data\EstimateInput;
use App\Pricing\EstimateInputFactory;
use App\Pricing\PricingEngine;
use App\Rules\NotAnEmailAddress;
use App\Rules\NotAPhoneNumber;
use App\Rules\UkNgPostcode;
use App\Rules\UkPhoneNumber;
use App\Services\QuoteRequestService;
use App\Support\Analytics\Analytics;
use App\Support\UkContactNormalizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.quote')]
#[Title('Get a free estimate')]
class EstimateWizard extends Component
{
    use WithFileUploads;

    /** @var list<TemporaryUploadedFile|null> */
    public array $propertyPhotos = [];

    public ?int $serviceId = null;

    public ?string $frequency = 'fortnightly';

    public ?string $propertyType = 'house';

    public int $bedrooms = 2;

    public bool $splitLevelFlat = false;

    public int $floors = 2;

    public int $bathrooms = 1;

    public int $wcs = 0;

    public int $kitchens = 1;

    public int $receptionRooms = 1;

    /** @var list<string> */
    public array $extraRooms = [];

    public ?string $propertyStatus = null;

    /** @var list<string> */
    public array $conditionFlags = [];

    public string $conditionNotes = '';

    /** @var list<int> */
    public array $addonIds = [];

    public ?string $preferredDate = null;

    public ?string $arrivalWindow = 'flexible';

    public string $firstName = '';

    public string $lastName = '';

    public string $phone = '';

    public string $email = '';

    public string $postcode = '';

    public string $addressLine1 = '';

    public string $addressLine2 = '';

    public string $city = 'Nottingham';

    public string $parkingNotes = '';

    public string $parkingOther = '';

    public string $accessNotes = '';

    public string $accessOther = '';

    public bool $extrasOpen = true;

    public bool $submitting = false;

    public ?string $savedReference = null;

    public ?string $whatsappNotice = null;

    /** Honeypot — must remain empty. */
    public string $website = '';

    public function mount(): void
    {
        $services = $this->services;

        if ($services->isEmpty()) {
            return;
        }

        $requested = request()->query('service');
        $match = $requested
            ? $services->firstWhere('slug', $requested)
            : null;

        $this->serviceId = ($match ?? $services->first())->id;
        $this->applyPropertyDefaults();

        $this->js(Analytics::scriptCall(Analytics::QUOTE_STARTED, [
            'service' => $this->selectedService()?->slug,
        ]));
    }

    public function updatedServiceId(): void
    {
        $this->forgetEstimateCaches();
        unset($this->availableAddons);

        $this->addonIds = [];
        $this->frequency = $this->selectedService()?->isRegularClean() ? 'fortnightly' : null;
        $this->propertyStatus = null;
    }

    public function updatedFrequency(): void
    {
        $this->forgetEstimateCaches();
    }

    public function updatedPropertyType(): void
    {
        $this->splitLevelFlat = false;
        $this->applyPropertyDefaults();
        $this->forgetEstimateCaches();
    }

    public function updatedSplitLevelFlat(bool $value): void
    {
        if ($value && $this->propertyType === PropertyType::Flat->value) {
            $this->floors = max($this->floors, 2);
        }

        $this->forgetEstimateCaches();
    }

    public function adjustQuantity(string $field, int $delta): void
    {
        $limits = [
            'bedrooms' => [0, 5],
            'floors' => [1, 5],
            'bathrooms' => [1, 6],
            'wcs' => [0, 4],
            'kitchens' => [1, 3],
            'receptionRooms' => [0, 6],
        ];

        if (! isset($limits[$field])) {
            return;
        }

        [$min, $max] = $limits[$field];
        $this->{$field} = max($min, min($max, (int) $this->{$field} + $delta));
        $this->forgetEstimateCaches();
    }

    public function updatedBedrooms(): void
    {
        $this->forgetEstimateCaches();
    }

    public function updatedFloors(): void
    {
        $this->forgetEstimateCaches();
    }

    public function updatedBathrooms(): void
    {
        $this->forgetEstimateCaches();
    }

    public function updatedWcs(): void
    {
        $this->forgetEstimateCaches();
    }

    public function updatedKitchens(): void
    {
        $this->forgetEstimateCaches();
    }

    public function updatedReceptionRooms(): void
    {
        $this->forgetEstimateCaches();
    }

    public function updatedExtraRooms(): void
    {
        $this->forgetEstimateCaches();
    }

    public function updatedPropertyStatus(): void
    {
        $this->forgetEstimateCaches();
    }

    public function updatedConditionFlags(): void
    {
        $this->forgetEstimateCaches();
    }

    public function updatedAddonIds(): void
    {
        $this->addonIds = array_values(array_map('intval', $this->addonIds));
        $this->extrasOpen = true;
        $this->forgetEstimateCaches();
    }

    public function toggleExtras(): void
    {
        $this->extrasOpen = ! $this->extrasOpen;
    }

    #[Computed]
    public function services(): Collection
    {
        return Service::query()
            ->active()
            ->select([
                'id',
                'name',
                'slug',
                'estimate_description',
                'is_active',
                'sort_order',
            ])
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function selectedService(): ?Service
    {
        if ($this->serviceId === null) {
            return null;
        }

        return $this->services->firstWhere('id', $this->serviceId);
    }

    #[Computed]
    public function visibleSections(): array
    {
        if ($this->selectedService()?->requiresManualQuote()) {
            return ['clean', 'property', 'when', 'details'];
        }

        return ['clean', 'property', 'rooms', 'condition', 'extras', 'when', 'details'];
    }

    #[Computed]
    public function availableAddons(): Collection
    {
        $service = $this->selectedService();

        if (! $service || $service->requiresManualQuote()) {
            return collect();
        }

        return $service->addons()->active()->orderBy('sort_order')->get();
    }

    #[Computed]
    public function estimateInput(): ?EstimateInput
    {
        $service = $this->selectedService();

        if (! $service || $this->bedrooms < 0) {
            return null;
        }

        $propertyType = PropertyType::tryFrom($this->propertyType ?? '');

        if (! $propertyType) {
            return null;
        }

        return EstimateInputFactory::make(
            service: $service,
            propertyType: $propertyType,
            bedrooms: $this->bedrooms,
            bathrooms: $this->bathrooms,
            wcs: $this->wcs,
            kitchens: $this->kitchens,
            receptionRooms: $this->receptionRooms,
            floors: $this->floors,
            extraRoomSlugs: $this->extraRooms,
            frequency: $this->frequency ? CleaningFrequency::tryFrom($this->frequency) : null,
            propertyStatus: $this->propertyStatus ? PropertyStatus::tryFrom($this->propertyStatus) : null,
            conditionFlagValues: $this->conditionFlags,
            addonIds: $this->addonIds,
            postcode: $this->postcode,
            preferredDate: $this->preferredDate,
            conditionNotes: $this->conditionNotes,
            parkingNotes: $this->resolvedParkingNotes(),
            accessNotes: $this->resolvedAccessNotes(),
            arrivalWindow: $this->arrivalWindow ? ArrivalWindow::tryFrom($this->arrivalWindow) : null,
        );
    }

    #[Computed]
    public function calculation(): ?CalculationResult
    {
        $input = $this->estimateInput;

        if (! $input) {
            return null;
        }

        try {
            return app(PricingEngine::class)->calculate($input);
        } catch (\Throwable) {
            return null;
        }
    }

    public function addonDisplayLabel(Addon $addon): string
    {
        return app(AddonPriceFormatter::class)->displayLabel($addon, $this->estimateInput);
    }

    public function submit(): void
    {
        if ($this->submitting) {
            return;
        }

        if ($this->isHoneypotTriggered()) {
            $this->redirect(route('home'), navigate: true);

            return;
        }

        if ($this->savedReference) {
            $this->redirect(route('quote.confirmation', $this->savedReference), navigate: true);

            return;
        }

        $this->ensureWithinQuoteRateLimit();

        $this->submitting = true;

        try {
            $this->validateAllSections();

            $quoteRequest = $this->persistLead(QuoteRequestSource::Web);

            $this->savedReference = $quoteRequest->reference;

            app(DispatchQuoteRequestNotifications::class)->handle($quoteRequest);

            $this->js(Analytics::scriptCall(Analytics::QUOTE_COMPLETED, [
                'reference' => $quoteRequest->reference,
                'channel' => 'web',
            ]));

            $this->redirect(route('quote.confirmation', $quoteRequest->reference));
        } catch (ValidationException $exception) {
            $this->dispatch('estimate-validation-failed');

            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'submit' => 'We could not send your estimate request just now. Please try again, or contact us by phone or WhatsApp.',
            ]);
        } finally {
            $this->submitting = false;
        }
    }

    public function submitViaWhatsApp(): void
    {
        if ($this->submitting) {
            return;
        }

        if ($this->isHoneypotTriggered()) {
            $this->whatsappNotice = 'Your request was saved and WhatsApp was opened.';

            return;
        }

        $this->ensureWithinQuoteRateLimit();

        $this->submitting = true;

        try {
            if ($this->savedReference) {
                $quoteRequest = QuoteRequest::query()
                    ->where('reference', $this->savedReference)
                    ->firstOrFail();

                $this->openWhatsAppFor($quoteRequest);

                return;
            }

            $this->validateAllSections();

            $quoteRequest = $this->persistLead(QuoteRequestSource::Whatsapp, whatsappInitiated: true);

            $this->savedReference = $quoteRequest->reference;

            app(DispatchQuoteRequestNotifications::class)->handle($quoteRequest);

            $this->js(Analytics::scriptCall(Analytics::QUOTE_COMPLETED, [
                'reference' => $quoteRequest->reference,
                'channel' => 'whatsapp',
            ]));
            $this->js(Analytics::scriptCall(Analytics::QUOTE_WHATSAPP_CLICKED, [
                'reference' => $quoteRequest->reference,
            ]));
            $this->js(Analytics::scriptCall(Analytics::WHATSAPP_QUOTE, [
                'reference' => $quoteRequest->reference,
            ]));

            $this->openWhatsAppFor($quoteRequest);
        } catch (ValidationException $exception) {
            $this->dispatch('estimate-validation-failed');

            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'submit' => 'We could not save your request just now. Please try again, or message us on WhatsApp directly.',
            ]);
        } finally {
            $this->submitting = false;
        }
    }

    protected function persistLead(QuoteRequestSource $source, bool $whatsappInitiated = false): QuoteRequest
    {
        $quoteRequest = app(QuoteRequestService::class)->createFromWizard(
            WizardSubmissionData::fromWizard($this),
            $source,
            $whatsappInitiated,
        );

        $photos = $this->resolvedPropertyPhotos();

        if ($photos !== []) {
            app(QuoteRequestService::class)->storePropertyPhotos($quoteRequest, $photos);
            $quoteRequest->refresh();

            $this->js(Analytics::scriptCall(Analytics::QUOTE_PHOTOS_ADDED, [
                'reference' => $quoteRequest->reference,
                'count' => count($quoteRequest->property_photo_paths ?? []),
            ]));
        }

        return $quoteRequest;
    }

    /**
     * @return list<TemporaryUploadedFile|UploadedFile>
     */
    protected function resolvedPropertyPhotos(): array
    {
        return array_values(array_filter(
            $this->propertyPhotos,
            fn ($file) => $file instanceof TemporaryUploadedFile || $file instanceof UploadedFile,
        ));
    }

    protected function isHoneypotTriggered(): bool
    {
        return filled($this->website);
    }

    protected function ensureWithinQuoteRateLimit(): void
    {
        $key = 'quote-submit:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'submit' => 'Too many estimate requests from this connection. Please wait a few minutes and try again.',
            ]);
        }

        RateLimiter::hit($key, 60);
    }

    protected function validateAllSections(): void
    {
        if (! $this->selectedService()?->isRegularClean()) {
            $this->frequency = null;
        }

        $rules = [];

        foreach ($this->visibleSections() as $sectionKey) {
            $rules = array_merge($rules, $this->rulesForSection($sectionKey));
        }

        $this->validate($rules);

        $this->phone = UkContactNormalizer::formatPhoneDisplay($this->phone);
        $this->email = UkContactNormalizer::normalizeEmail($this->email);
        $this->postcode = UkContactNormalizer::normalizePostcode($this->postcode);
    }

    protected function openWhatsAppFor(QuoteRequest $quoteRequest): void
    {
        $url = app(QuoteRequestService::class)->whatsappUrl($quoteRequest);

        $this->whatsappNotice = 'Your request was saved and WhatsApp was opened.';
        $this->dispatch('open-whatsapp', url: $url);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rulesForSection(string $sectionKey): array
    {
        return match ($sectionKey) {
            'clean' => [
                'serviceId' => [
                    'required',
                    'integer',
                    Rule::in($this->services->pluck('id')->all()),
                ],
                'frequency' => [
                    Rule::requiredIf(fn () => $this->selectedService()?->isRegularClean()),
                    'nullable',
                    Rule::in(array_column(CleaningFrequency::cases(), 'value')),
                ],
            ],
            'property' => [
                'propertyType' => ['required', Rule::in(array_column(PropertyType::cases(), 'value'))],
                'bedrooms' => ['required', 'integer', 'min:0', 'max:5'],
                'floors' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:5',
                    Rule::when(
                        $this->propertyType === PropertyType::Flat->value && ! $this->splitLevelFlat,
                        ['in:1'],
                    ),
                    Rule::when(
                        $this->propertyType === PropertyType::Flat->value && $this->splitLevelFlat,
                        ['min:2'],
                    ),
                ],
                'splitLevelFlat' => ['boolean'],
            ],
            'rooms' => [
                'bathrooms' => ['required', 'integer', 'min:1', 'max:6'],
                'wcs' => ['required', 'integer', 'min:0', 'max:4'],
                'kitchens' => ['required', 'integer', 'min:1', 'max:3'],
                'receptionRooms' => ['required', 'integer', 'min:0', 'max:6'],
                'extraRooms' => ['array'],
                'extraRooms.*' => [Rule::in(array_column(ExtraRoomType::cases(), 'value'))],
            ],
            'condition' => [
                'propertyStatus' => [
                    Rule::requiredIf(fn () => $this->selectedService()?->appliesPropertyStatusMultipliers()),
                    'nullable',
                    Rule::in(array_column(PropertyStatus::cases(), 'value')),
                ],
                'conditionFlags' => ['array'],
                'conditionFlags.*' => [Rule::in(array_column(ConditionFlag::cases(), 'value'))],
                'conditionNotes' => ['nullable', 'string', 'max:2000'],
            ],
            'extras' => [
                'addonIds' => ['array'],
                'addonIds.*' => [
                    'integer',
                    Rule::in($this->availableAddons->pluck('id')->all()),
                ],
            ],
            'when' => [
                'preferredDate' => ['required', 'date', 'after_or_equal:today'],
                'arrivalWindow' => ['required', Rule::in(array_column(ArrivalWindow::cases(), 'value'))],
            ],
            'details' => [
                'firstName' => ['required', 'string', 'max:100'],
                'lastName' => ['required', 'string', 'max:100'],
                'phone' => ['required', 'string', 'max:30', new UkPhoneNumber, new NotAnEmailAddress],
                'email' => ['required', 'email', 'max:255', new NotAPhoneNumber],
                'postcode' => ['required', 'string', 'max:10', new UkNgPostcode],
                'addressLine1' => ['required', 'string', 'max:255'],
                'addressLine2' => ['nullable', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:100'],
                'parkingNotes' => ['nullable', 'string', 'max:500', Rule::in(['', ...ParkingOption::labels()])],
                'parkingOther' => ['nullable', 'string', 'max:400'],
                'accessNotes' => ['nullable', 'string', 'max:500', Rule::in(['', ...AccessOption::labels()])],
                'accessOther' => ['nullable', 'string', 'max:400'],
                'propertyPhotos' => ['nullable', 'array', 'max:8'],
                'propertyPhotos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            ],
            default => [],
        };
    }

    protected function applyPropertyDefaults(): void
    {
        $type = PropertyType::tryFrom($this->propertyType ?? '');

        if (! $type) {
            return;
        }

        $this->floors = $type->defaultFloors();
        $this->splitLevelFlat = false;
    }

    public function resolvedParkingNotes(): string
    {
        return $this->composeOptionalNote($this->parkingNotes, $this->parkingOther);
    }

    public function resolvedAccessNotes(): string
    {
        return $this->composeOptionalNote($this->accessNotes, $this->accessOther);
    }

    protected function composeOptionalNote(string $option, string $other): string
    {
        $option = trim($option);
        $other = trim($other);

        if ($option === ParkingOption::Other->label() || $option === AccessOption::Other->label()) {
            return $other !== '' ? 'Other: '.$other : $option;
        }

        return $option;
    }

    protected function forgetEstimateCaches(): void
    {
        unset($this->services);
        unset($this->selectedService);
        unset($this->visibleSections);
        unset($this->availableAddons);
        unset($this->estimateInput);
        unset($this->calculation);
    }

    public function render()
    {
        return view('livewire.estimate-wizard');
    }
}
