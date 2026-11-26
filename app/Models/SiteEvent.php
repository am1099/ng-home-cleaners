<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'occurred_at',
    'event',
    'path',
    'source',
    'medium',
    'campaign',
    'device',
    'visitor_key',
    'from_ads',
])]
class SiteEvent extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'from_ads' => 'boolean',
        ];
    }
}
