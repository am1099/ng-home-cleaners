<?php

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Concerns\RedirectsToViewOrIndexAfterCreate;
use App\Filament\Resources\Faqs\FaqResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFaq extends CreateRecord
{
    use RedirectsToViewOrIndexAfterCreate;

    protected static string $resource = FaqResource::class;
}
