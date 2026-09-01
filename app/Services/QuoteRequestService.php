<?php

namespace App\Services;

use App\Data\WizardSubmissionData;
use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Pricing\PricingEngine;
use App\Support\Media;
use App\Support\UkContactNormalizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class QuoteRequestService
{
    public function __construct(
        private readonly QuoteReferenceGenerator $referenceGenerator,
        private readonly PricingEngine $pricingEngine,
        private readonly CustomerMatcher $customerMatcher,
    ) {}

    public function createFromWizard(
        WizardSubmissionData $data,
        QuoteRequestSource $source,
        bool $whatsappInitiated = false,
    ): QuoteRequest {
        $service = Service::query()->active()->findOrFail($data->serviceId);

        $calculation = $this->pricingEngine->calculate($data->toEstimateInput());

        return DB::transaction(function () use ($data, $source, $whatsappInitiated, $service, $calculation): QuoteRequest {
            $customer = $this->reconcileCustomer([
                'first_name' => $data->firstName,
                'last_name' => $data->lastName,
                'phone' => $data->phone,
                'email' => $data->email,
                'postcode' => $data->postcode,
                'address_line1' => $data->addressLine1,
                'address_line2' => $data->addressLine2,
                'city' => $data->city,
            ]);

            return QuoteRequest::query()->create([
                'reference' => $this->referenceGenerator->next(),
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'source' => $source,
                'status' => QuoteRequestStatus::New,
                'first_name' => $data->firstName,
                'last_name' => $data->lastName,
                'phone' => $data->phone,
                'email' => $data->email,
                'postcode' => $data->postcode,
                'address_line1' => $data->addressLine1,
                'address_line2' => $data->addressLine2,
                'city' => $data->city,
                'parking_notes' => $data->parkingNotes ?: null,
                'access_notes' => $data->accessNotes ?: null,
                'preferred_date' => $data->preferredDate,
                'arrival_window' => $data->arrivalWindow,
                'frequency' => $data->frequency,
                'property_type' => $data->propertyType,
                'bedrooms' => $data->bedrooms,
                'split_level_flat' => $data->splitLevelFlat,
                'floors' => $data->floors,
                'bathrooms' => $data->bathrooms,
                'wcs' => $data->wcs,
                'kitchens' => $data->kitchens,
                'reception_rooms' => $data->receptionRooms,
                'extra_rooms' => $data->extraRooms,
                'property_status' => $data->propertyStatus,
                'condition_flags' => $data->conditionFlags,
                'condition_notes' => $data->conditionNotes ?: null,
                'addon_ids' => $data->addonIds,
                'selections_snapshot' => $data->selectionsSnapshot(),
                'pricing_snapshot' => $calculation->snapshot,
                'guide_estimate_headline' => $calculation->displayHeadline,
                'guide_estimate_detail' => $calculation->displayDetail,
                'guide_estimate_min_pence' => $calculation->finalRange->minPence,
                'guide_estimate_max_pence' => $calculation->finalRange->maxPence,
                'guide_single_price_pence' => $calculation->finalSinglePricePence,
                'is_numeric_estimate' => $calculation->isNumericEstimate,
                'whatsapp_initiated_at' => $whatsappInitiated ? now() : null,
                'submitted_at' => now(),
            ]);
        });
    }

    /**
     * @param  list<UploadedFile|TemporaryUploadedFile|null>  $files
     */
    public function storePropertyPhotos(QuoteRequest $quoteRequest, array $files): void
    {
        $paths = [];
        $disk = Media::diskName();

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile && ! $file instanceof TemporaryUploadedFile) {
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = Str::uuid()->toString().'.'.$extension;
            $stored = $file->storeAs('quote-requests/'.$quoteRequest->reference, $filename, [
                'disk' => $disk,
            ]);

            if (! is_string($stored) || $stored === '') {
                report(new \RuntimeException("Quote photo upload failed on disk [{$disk}]."));

                continue;
            }

            $paths[] = $stored;
        }

        if ($paths === []) {
            return;
        }

        if (! Schema::hasColumn('quote_requests', 'property_photo_paths')) {
            report(new \RuntimeException('quote_requests.property_photo_paths column is missing. Run php artisan migrate --force on this environment.'));

            return;
        }

        $quoteRequest->update(['property_photo_paths' => $paths]);
    }

    /**
     * @param  array{
     *     first_name: string,
     *     last_name: string,
     *     phone: string,
     *     email?: string|null,
     *     postcode?: string|null,
     *     address_line1?: string|null,
     *     address_line2?: string|null,
     *     city?: string|null,
     *     notes?: string|null,
     *     customer_id?: int|null,
     *     service_id: int,
     *     source: QuoteRequestSource|string,
     *     preferred_date?: string|null,
     *     arrival_window?: string|null,
     *     frequency?: string|null,
     *     property_type?: string|null,
     *     bedrooms?: int|null,
     *     floors?: int|null,
     *     bathrooms?: int|null,
     *     wcs?: int|null,
     *     kitchens?: int|null,
     *     reception_rooms?: int|null,
     *     condition_notes?: string|null,
     *     parking_notes?: string|null,
     *     access_notes?: string|null,
     *     internal_notes?: string|null,
     *     guide_estimate_headline?: string|null,
     *     final_quote_amount_pence?: int|null,
     *     status?: QuoteRequestStatus|string|null,
     * }  $data
     */
    public function createManual(array $data): QuoteRequest
    {
        $source = $data['source'] instanceof QuoteRequestSource
            ? $data['source']
            : QuoteRequestSource::from($data['source']);

        if (! in_array($source, [QuoteRequestSource::Phone, QuoteRequestSource::Manual], true)) {
            throw new \InvalidArgumentException('Manual leads must use phone or manual source.');
        }

        $service = Service::query()->findOrFail($data['service_id']);
        $phoneDisplay = UkContactNormalizer::formatPhoneDisplay($data['phone']);
        $email = filled($data['email'] ?? null)
            ? UkContactNormalizer::normalizeEmail((string) $data['email'])
            : null;
        $postcode = filled($data['postcode'] ?? null)
            ? UkContactNormalizer::normalizePostcode((string) $data['postcode'])
            : null;

        return DB::transaction(function () use ($data, $source, $service, $phoneDisplay, $email, $postcode): QuoteRequest {
            if (! empty($data['customer_id'])) {
                $customer = Customer::query()->findOrFail($data['customer_id']);
            } else {
                $customer = $this->reconcileCustomer([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone' => $phoneDisplay,
                    'email' => $email,
                    'postcode' => $postcode,
                    'address_line1' => $data['address_line1'] ?? null,
                    'address_line2' => $data['address_line2'] ?? null,
                    'city' => $data['city'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);
            }

            $status = isset($data['status'])
                ? ($data['status'] instanceof QuoteRequestStatus
                    ? $data['status']
                    : QuoteRequestStatus::from((string) $data['status']))
                : QuoteRequestStatus::New;

            $lead = QuoteRequest::query()->create([
                'reference' => $this->referenceGenerator->next(),
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'source' => $source,
                'status' => $status,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $phoneDisplay,
                'email' => $email,
                'postcode' => $postcode,
                'address_line1' => $data['address_line1'] ?? null,
                'address_line2' => $data['address_line2'] ?? null,
                'city' => $data['city'] ?? null,
                'parking_notes' => $data['parking_notes'] ?? null,
                'access_notes' => $data['access_notes'] ?? null,
                'preferred_date' => $data['preferred_date'] ?? null,
                'arrival_window' => $data['arrival_window'] ?? null,
                'frequency' => $data['frequency'] ?? null,
                'property_type' => $data['property_type'] ?? null,
                'bedrooms' => $data['bedrooms'] ?? null,
                'split_level_flat' => false,
                'floors' => $data['floors'] ?? null,
                'bathrooms' => $data['bathrooms'] ?? null,
                'wcs' => $data['wcs'] ?? null,
                'kitchens' => $data['kitchens'] ?? null,
                'reception_rooms' => $data['reception_rooms'] ?? null,
                'extra_rooms' => [],
                'property_status' => null,
                'condition_flags' => [],
                'condition_notes' => $data['condition_notes'] ?? null,
                'addon_ids' => [],
                'selections_snapshot' => [
                    'manual' => true,
                    'source' => $source->value,
                ],
                'pricing_snapshot' => [
                    'manual' => true,
                ],
                'guide_estimate_headline' => $data['guide_estimate_headline'] ?? 'To be quoted',
                'guide_estimate_detail' => 'Entered manually in CRM.',
                'is_numeric_estimate' => false,
                'final_quote_amount_pence' => $data['final_quote_amount_pence'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'submitted_at' => now(),
            ]);

            if ($status !== QuoteRequestStatus::New) {
                $lead->recordStatusTimestamp($status);
                $lead->save();
            }

            return $lead->fresh(['customer', 'service']);
        });
    }

    /**
     * @param  array{
     *     first_name: string,
     *     last_name: string,
     *     phone: string,
     *     email?: string|null,
     *     postcode?: string|null,
     *     address_line1?: string|null,
     *     address_line2?: string|null,
     *     city?: string|null,
     *     notes?: string|null,
     * }  $data
     */
    public function reconcileCustomer(array $data): Customer
    {
        $phoneDisplay = UkContactNormalizer::formatPhoneDisplay($data['phone']);
        $normalizedPhone = UkContactNormalizer::normalizePhone($phoneDisplay);
        $email = filled($data['email'] ?? null)
            ? UkContactNormalizer::normalizeEmail((string) $data['email'])
            : null;

        $customer = $this->customerMatcher->findMatch($email, $normalizedPhone);

        $attributes = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone_normalized' => $normalizedPhone,
            'phone_display' => $phoneDisplay,
            'postcode' => $data['postcode'] ?? null,
            'address_line1' => $data['address_line1'] ?? null,
            'address_line2' => $data['address_line2'] ?? null,
            'city' => $data['city'] ?? null,
        ];

        if ($email) {
            $attributes['email'] = $email;
        }

        if (array_key_exists('notes', $data) && filled($data['notes'])) {
            $attributes['notes'] = $data['notes'];
        }

        if ($customer) {
            $customer->update($attributes);

            return $customer->fresh();
        }

        return Customer::query()->create([
            ...$attributes,
            'email' => $email,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function whatsappMessage(QuoteRequest $quoteRequest): string
    {
        $serviceName = $quoteRequest->service?->name ?? 'cleaning enquiry';

        $photoNote = ! empty($quoteRequest->property_photo_paths)
            ? ' Photos uploaded with the form are already saved against this request.'
            : '';

        return sprintf(
            "Hi, I've just submitted a cleaning estimate request (Ref: %s) for a %s. My guide estimate was %s. Please could you confirm availability? Please also send a short walkthrough video of the property on WhatsApp (kitchen, bathrooms, main rooms) so we can give you an accurate fixed quote.%s",
            $quoteRequest->reference,
            strtolower($serviceName),
            $quoteRequest->guide_estimate_headline ?? 'to be confirmed',
            $photoNote,
        );
    }

    public function whatsappUrl(QuoteRequest $quoteRequest): string
    {
        $settings = SiteSetting::instance();
        $message = $this->whatsappMessage($quoteRequest);

        return $settings->whatsappUrlWithMessage($message);
    }
}
