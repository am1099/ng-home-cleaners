<?php

namespace App\Filament\Concerns;

trait RedirectsToViewOrIndexAfterCreate
{
    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        if ($resource::hasPage('view') && $resource::canView($this->getRecord())) {
            return $this->getResourceUrl('view', $this->getRedirectUrlParameters());
        }

        return $this->getResourceUrl('index');
    }
}
