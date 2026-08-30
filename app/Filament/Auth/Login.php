<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getHeading(): string|Htmlable
    {
        return 'NG Home Cleaners';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Staff login for quotes, bookings and website content.';
    }
}
