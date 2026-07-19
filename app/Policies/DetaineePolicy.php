<?php

namespace App\Policies;

use App\Models\Detainee;
use App\Models\User;

class DetaineePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Detainee $detainee): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Detainee $detainee): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Detainee $detainee): bool
    {
        return $user->isAdmin();
    }
}
