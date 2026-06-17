<?php

namespace App\Policies;

use App\Models\NumberReservation;
use App\Models\User;

class NumberReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'staff'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'staff'], true);
    }

    public function approve(User $user, NumberReservation $numberReservation): bool
    {
        return $user->role === 'admin';
    }

    public function fillSlot(User $user, NumberReservation $numberReservation): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return $user->role === 'staff' && $numberReservation->requested_by === $user->id;
    }
}
