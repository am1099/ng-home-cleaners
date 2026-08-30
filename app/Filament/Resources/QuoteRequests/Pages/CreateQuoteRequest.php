<?php

namespace App\Filament\Resources\QuoteRequests\Pages;

use App\Filament\Concerns\RedirectsToViewOrIndexAfterCreate;
use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use App\Filament\Resources\QuoteRequests\Schemas\QuoteRequestForm;
use App\Services\QuoteRequestService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class CreateQuoteRequest extends CreateRecord
{
    use RedirectsToViewOrIndexAfterCreate;

    protected static string $resource = QuoteRequestResource::class;

    protected static ?string $title = 'Add phone / manual lead';

    public function form(Schema $schema): Schema
    {
        return QuoteRequestForm::configureManualCreate($schema);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(QuoteRequestService::class)->createManual($data);
    }
}
