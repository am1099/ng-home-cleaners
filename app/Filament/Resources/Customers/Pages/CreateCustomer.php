<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Concerns\RedirectsToViewOrIndexAfterCreate;
use App\Filament\Resources\Customers\CustomerResource;
use App\Support\UkContactNormalizer;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    use RedirectsToViewOrIndexAfterCreate;

    protected static string $resource = CustomerResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['phone_display'] = UkContactNormalizer::formatPhoneDisplay((string) $data['phone_display']);
        $data['phone_normalized'] = UkContactNormalizer::normalizePhone($data['phone_display']);

        if (filled($data['email'] ?? null)) {
            $data['email'] = UkContactNormalizer::normalizeEmail((string) $data['email']);
        } else {
            $data['email'] = null;
        }

        if (filled($data['postcode'] ?? null)) {
            $data['postcode'] = UkContactNormalizer::normalizePostcode((string) $data['postcode']);
        }

        return $data;
    }
}
