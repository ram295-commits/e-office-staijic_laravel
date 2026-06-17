<?php

namespace App\Policies;

use App\Models\Disposition;
use App\Models\User;

class DispositionPolicy
{
    public function respond(User $user, Disposition $disposition): bool
    {
        return $disposition->to_user_id === $user->id;
    }

    public function delete(User $user, Disposition $disposition): bool
    {
        return $disposition->from_user_id === $user->id
            && $disposition->status === 'pending';
    }
}
