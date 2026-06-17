<?php

namespace App\Notifications;

use App\Models\Disposition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DispositionCreated extends Notification implements ShouldQueue
{
    use Queueable;

    private $disposition;

    /**
     * Create a new notification instance.
     */
    public function __construct(Disposition $disposition)
    {
        $this->disposition = $disposition;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $mail = $this->disposition->mail;
        $fromUser = $this->disposition->fromUser;

        return [
            'type'             => 'disposition_created',
            'disposition_id'   => $this->disposition->id,
            'mail_id'          => $this->disposition->mail_id,
            'mail_reference'   => $mail ? $mail->reference_number : null,
            'mail_subject'     => $mail ? $mail->subject : null,
            'sender_name'      => $fromUser ? $fromUser->name : 'System',
            'instruction'      => $this->disposition->instruction,
            'action_type'      => $this->disposition->action_type,
            'due_date'         => $this->disposition->due_date ? $this->disposition->due_date->format('Y-m-d') : null,
            'message'          => 'Anda menerima disposisi baru mengenai surat "' . ($mail ? $mail->subject : '') . '" dari ' . ($fromUser ? $fromUser->name : 'System'),
        ];
    }
}
