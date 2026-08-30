<?php

namespace App\Filament\Resources\ServiceAreas\Pages;

use App\Filament\Concerns\RedirectsToViewOrIndexAfterCreate;
use App\Filament\Resources\ServiceAreas\ServiceAreaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceArea extends CreateRecord
{
    use RedirectsToViewOrIndexAfterCreate;

    protected static string $resource = ServiceAreaResource::class;
}
