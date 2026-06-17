<?php

namespace App\Policies;

use App\Models\Disposition;
use App\Models\Mail;
use App\Models\User;

class MailPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'staff'], true);
    }

    public function view(User $user, Mail $mail): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        return $this->canAccessMail($user, $mail);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'staff'], true);
    }

    public function update(User $user, Mail $mail): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            return $this->mailInUserUnits($user, $mail);
        }

        return $this->canAccessMail($user, $mail);
    }

    public function delete(User $user, Mail $mail): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            return $this->mailInUserUnits($user, $mail);
        }

        return $mail->created_by === $user->id && $mail->status === 'draft';
    }

    public function restore(User $user, Mail $mail): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Mail $mail): bool
    {
        return $user->isAdmin();
    }

    public function updateStatus(User $user, Mail $mail): bool
    {
        return $this->update($user, $mail);
    }

    public function updateReferenceNumber(User $user, Mail $mail): bool
    {
        return $user->isAdmin();
    }

    public function exportArchive(User $user): bool
    {
        return $user->isManager();
    }

    /**
     * Staff may access mail they created, are assigned to, received a disposition for, or belongs to their unit.
     */
    private function canAccessMail(User $user, Mail $mail): bool
    {
        if ($mail->created_by === $user->id) {
            return true;
        }

        if ($mail->assigned_to === $user->id) {
            return true;
        }

        if (Disposition::where('mail_id', $mail->id)->where('to_user_id', $user->id)->exists()) {
            return true;
        }

        return $this->mailInUserUnits($user, $mail);
    }

    private function mailInUserUnits(User $user, Mail $mail): bool
    {
        if (!$mail->unit_id) {
            return false;
        }

        return $user->units()->where('units.id', $mail->unit_id)->exists();
    }
}
