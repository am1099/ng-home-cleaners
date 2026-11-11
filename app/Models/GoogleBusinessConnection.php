<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'refresh_token',
    'account_name',
    'account_resource',
    'location_title',
    'location_resource',
    'connected_at',
])]
class GoogleBusinessConnection extends Model
{
    protected function casts(): array
    {
        return [
            'refresh_token' => 'encrypted',
            'connected_at' => 'datetime',
        ];
    }

    public static function current(): ?self
    {
        return static::query()->latest('id')->first();
    }
}
