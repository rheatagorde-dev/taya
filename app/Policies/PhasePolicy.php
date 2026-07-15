<?php

namespace App\Policies;

use App\Models\DetaineePhase;
use App\Models\User;

class PhasePolicy
{
    public function complete(User $user, DetaineePhase $phase): bool
    {
        return $user->isAdmin();
    }

    public function flag(User $user, DetaineePhase $phase): bool
    {
        return $user->isAdmin();
    }
}
