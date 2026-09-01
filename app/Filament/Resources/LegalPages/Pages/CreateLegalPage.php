<?php

namespace App\Filament\Resources\LegalPages\Pages;

use App\Filament\Concerns\RedirectsToViewOrIndexAfterCreate;
use App\Filament\Resources\LegalPages\LegalPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLegalPage extends CreateRecord
{
    use RedirectsToViewOrIndexAfterCreate;

    protected static string $resource = LegalPageResource::class;
}
