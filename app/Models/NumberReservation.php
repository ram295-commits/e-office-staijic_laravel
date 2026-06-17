<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NumberReservation extends Model
{
    protected $fillable = [
        'letter_format_id',
        'document_type_id',
        'requested_by',
        'approved_by',
        'quantity',
        'status',
        'reserved_slots',
        'backdate_target',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'reserved_slots'  => 'array',
            'backdate_target' => 'date',
        ];
    }

    public function letterFormat(): BelongsTo
    {
        return $this->belongsTo(LetterFormat::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function mails(): HasMany
    {
        return $this->hasMany(Mail::class, 'reservation_slot_id');
    }
}
