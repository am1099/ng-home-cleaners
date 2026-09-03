<?php

namespace App\Filament\Resources\EmailTemplates\Pages;

use App\Filament\Concerns\HasToggleableRecordLayout;
use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Services\EmailTemplateService;
use Filament\Resources\Pages\ListRecords;

class ListEmailTemplates extends ListRecords
{
    use HasToggleableRecordLayout;

    protected static string $resource = EmailTemplateResource::class;

    protected static ?string $title = 'Email templates';

    public function mount(): void
    {
        parent::mount();

        app(EmailTemplateService::class)->ensureDefaults();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
