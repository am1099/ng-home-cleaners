<?php

namespace App\Filament\Support;

use App\Models\ServiceArea;
use Filament\Forms\Components\Select;

final class ServiceAreaSelect
{
    public static function make(string $name = 'service_area_id'): Select
    {
        return Select::make($name)
            ->relationship('serviceArea', 'name')
            ->getOptionLabelFromRecordUsing(
                fn (ServiceArea $record): string => self::label($record)
            )
            ->searchable(['postcode_label', 'name'])
            ->preload();
    }

    public static function label(ServiceArea $area): string
    {
        return trim($area->postcode_label).' - '.trim($area->name);
    }
}
