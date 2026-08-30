<?php

namespace App\Filament\Support;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;

final class StandardRecordActions
{
    /**
     * @return array<int, ViewAction|EditAction|DeleteAction>
     */
    public static function make(bool $withView = true): array
    {
        $actions = [];

        if ($withView) {
            $actions[] = ViewAction::make()
                ->iconButton()
                ->tooltip('View');
        }

        $actions[] = EditAction::make()
            ->iconButton()
            ->tooltip('Edit');

        $actions[] = DeleteAction::make()
            ->iconButton()
            ->tooltip('Delete');

        return $actions;
    }
}
