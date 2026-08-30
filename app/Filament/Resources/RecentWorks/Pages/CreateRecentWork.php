<?php

namespace App\Filament\Resources\RecentWorks\Pages;

use App\Filament\Concerns\RedirectsToViewOrIndexAfterCreate;
use App\Filament\Resources\RecentWorks\RecentWorkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRecentWork extends CreateRecord
{
    use RedirectsToViewOrIndexAfterCreate;

    protected static string $resource = RecentWorkResource::class;
}
