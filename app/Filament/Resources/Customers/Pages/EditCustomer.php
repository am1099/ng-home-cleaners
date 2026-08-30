<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Support\UkContactNormalizer;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
