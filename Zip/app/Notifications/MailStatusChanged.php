<?php

namespace App\Notifications;

use App\Models\Mail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MailStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    private $mail;
    private $changedBy;
    private $oldStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Mail $mail, User $changedBy, string $oldStatus)
    {
        $this->mail = $mail;
        $this->changedBy = $changedBy;
        $this->oldStatus = $oldStatus;
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
        return [
            'type'            => 'mail_status_changed',
            'mail_id'         => $this->mail->id,
            'mail_reference'  => $this->mail->reference_number,
            'mail_subject'    => $this->mail->subject,
            'old_status'      => $this->oldStatus,
            'new_status'      => $this->mail->status,
            'new_status_label'=> $this->mail->status_label,
            'changed_by_name' => $this->changedBy->name,
            'message'         => 'Status surat "' . $this->mail->subject . '" telah diubah menjadi "' . $this->mail->status_label . '" oleh ' . $this->changedBy->name,
        ];
    }
}
