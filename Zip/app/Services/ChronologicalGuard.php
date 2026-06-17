<?php

namespace App\Services;

use App\Models\Mail;
use Carbon\Carbon;

class ChronologicalGuard
{
    /**
     * Validate that a target date sits correctly between a previous mail date and a next mail date.
     *
     * @param Carbon $target
     * @param Mail|null $prevMail
     * @param Mail|null $nextMail
     * @return bool
     */
    public function validate(Carbon $target, ?Mail $prevMail, ?Mail $nextMail): bool
    {
        // If there's a previous mail, target date must be >= prev mail date
        if ($prevMail && $target->lt($prevMail->tanggal_surat)) {
            return false;
        }

        // If there's a next mail, target date must be <= next mail date
        if ($nextMail && $target->gt($nextMail->tanggal_surat)) {
            return false;
        }

        return true;
    }

    /**
     * Validate that a reserved sequence range is chronologically correct for the target date.
     *
     * @param Carbon $targetDate
     * @param int $unitId
     * @param int $startSequence
     * @param int $quantity
     * @return bool
     */
    public function validateForReservation(Carbon $targetDate, int $unitId, int $startSequence, int $quantity): bool
    {
        $year = $targetDate->year;

        // Find the mail with the largest sequence number < $startSequence
        $prevMail = Mail::where('unit_id', $unitId)
            ->whereYear('tanggal_surat', $year)
            ->where('sequence_number', '<', $startSequence)
            ->orderByDesc('sequence_number')
            ->first();

        // Find the mail with the smallest sequence number >= $startSequence + $quantity
        $nextMail = Mail::where('unit_id', $unitId)
            ->whereYear('tanggal_surat', $year)
            ->where('sequence_number', '>=', $startSequence + $quantity)
            ->orderBy('sequence_number')
            ->first();

        return $this->validate($targetDate, $prevMail, $nextMail);
    }
}
