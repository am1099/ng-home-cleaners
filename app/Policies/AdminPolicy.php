<?php

namespace App\Policies;

use App\Models\User;

/**
 * v1 CRM access: any authenticated Filament user may manage content.
 * Registration is disabled; only seeded/invited staff accounts exist.
 * Introduce roles before inviting additional staff.
 */
abstract class AdminPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->exists ? true : false;
    }
}
