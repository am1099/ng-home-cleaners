<?php

namespace App\Filament\Resources\Addons\Pages;

use App\Filament\Concerns\RedirectsToViewOrIndexAfterCreate;
use App\Filament\Resources\Addons\AddonResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAddon extends CreateRecord
{
    use RedirectsToViewOrIndexAfterCreate;

    protected static string $resource = AddonResource::class;
}
