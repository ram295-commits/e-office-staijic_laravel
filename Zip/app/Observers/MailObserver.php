<?php

namespace App\Observers;

use App\Models\Mail;
use App\Models\MailContentLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MailObserver
{
    /**
     * Handle the Mail "updated" event.
     */
    public function updated(Mail $mail): void
    {
        $changes = [];
        $oldValues = [];

        $fieldMap = [
            'subject'          => 'subject',
            'body'             => 'body',
            'status'           => 'status',
            'reference_number' => 'reference_number',
            'tanggal_surat'    => 'date',
            'assigned_to_unit' => 'assigned_to_unit',
        ];

        foreach ($fieldMap as $modelField => $logField) {
            if ($mail->wasChanged($modelField)) {
                $newVal = $mail->getChanges()[$modelField];
                $oldVal = $mail->getOriginal($modelField);

                // Format dates to Y-m-d string for consistency
                if ($modelField === 'tanggal_surat') {
                    $newVal = $newVal ? \Carbon\Carbon::parse($newVal)->format('Y-m-d') : null;
                    $oldVal = $oldVal ? \Carbon\Carbon::parse($oldVal)->format('Y-m-d') : null;
                }

                $changes[$logField] = $newVal;
                $oldValues[$logField] = $oldVal;
            }
        }

        if (empty($changes)) {
            return;
        }

        // Log INSERT must only be committed if the outer transaction succeeds
        DB::afterCommit(function () use ($mail, $changes, $oldValues) {
            MailContentLog::create([
                'mail_id'    => $mail->id,
                'changed_by' => Auth::id() ?? $mail->created_by,
                'action'     => 'update',
                'changes'    => $changes,
                'old_values' => $oldValues,
            ]);
        });
    }
}
