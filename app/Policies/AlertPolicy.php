<?php

namespace App\Policies;

use App\Models\Alert;
use App\Models\User;

class AlertPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Alert $alert): bool
    {
        return true;
    }

    public function assign(User $user, Alert $alert): bool
    {
        return $user->hasRole('admin', 'pao_lawyer', 'ngo_lawyer');
    }

    public function resolve(User $user, Alert $alert): bool
    {
        return $user->hasRole('admin', 'pao_lawyer', 'ngo_lawyer');
    }

    public function override(User $user, Alert $alert): bool
    {
        return $user->isAdmin();
    }
}
