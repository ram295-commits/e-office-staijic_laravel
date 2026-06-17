<?php

namespace App\Notifications;

use App\Models\NumberReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NumberReservationRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public NumberReservation $reservation;

    /**
     * Create a new notification instance.
     */
    public function __construct(NumberReservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'number_reservation_id' => $this->reservation->id,
            'quantity'              => $this->reservation->quantity,
            'backdate_target'       => $this->reservation->backdate_target->format('Y-m-d'),
            'requested_by'          => $this->reservation->requester->name ?? 'Unknown',
            'reason'                => $this->reservation->reason,
            'message'               => "New number reservation request from " . ($this->reservation->requester->name ?? 'Unknown') . " for " . $this->reservation->quantity . " slots on " . $this->reservation->backdate_target->format('Y-m-d'),
        ];
    }
}
