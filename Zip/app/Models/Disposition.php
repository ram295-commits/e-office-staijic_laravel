<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disposition extends Model
{
    protected $fillable = [
        'mail_id', 'from_user_id', 'to_user_id', 'instruction',
        'action_type', 'due_date', 'status', 'response_notes',
        'responded_at', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date'     => 'date',
            'responded_at' => 'datetime',
            'read_at'      => 'datetime',
        ];
    }

    // Relationships
    public function mail(): BelongsTo
    {
        return $this->belongsTo(Mail::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    // Helpers
    public function getActionLabelAttribute(): string
    {
        return match ($this->action_type) {
            'for_review'     => 'Untuk Ditelaah',
            'for_action'     => 'Untuk Ditindaklanjuti',
            'for_information'=> 'Untuk Diketahui',
            'for_approval'   => 'Untuk Disetujui',
            'for_filing'     => 'Untuk Diarsipkan',
            'for_reply'      => 'Untuk Dibalas',
            'coordinate'     => 'Koordinasikan',
            default          => 'Lainnya',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'Menunggu',
            'in_progress' => 'Diproses',
            'completed'   => 'Selesai',
            'cancelled'   => 'Dibatalkan',
            default       => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'yellow',
            'in_progress' => 'blue',
            'completed'   => 'green',
            'cancelled'   => 'red',
            default       => 'gray',
        };
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }
}
