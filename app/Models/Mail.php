<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference_number', 'type', 'document_type_id', 'unit_id', 'sequence_number', 'sender_unit', 'jenjang', 'subject', 'body',
        'sender_name', 'sender_organization', 'sender_email',
        'recipient_name', 'recipient_department', 'recipient_email',
        'tanggal_surat', 'received_date', 'priority', 'classification', 'status', 'is_backdated',
        'attachment_path', 'attachment_name', 'created_by', 'assigned_to', 'assigned_to_unit',
        'notes', 'read_at', 'reservation_slot_id', 'date_locked',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function assignedUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'assigned_to_unit');
    }

    public function numberReservation(): BelongsTo
    {
        return $this->belongsTo(NumberReservation::class, 'reservation_slot_id');
    }

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'received_date' => 'date',
            'read_at'       => 'datetime',
            'is_backdated'  => 'boolean',
            'date_locked'   => 'boolean',
        ];
    }

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function dispositions(): HasMany
    {
        return $this->hasMany(Disposition::class);
    }

    public function referenceLogs(): HasMany
    {
        return $this->hasMany(MailReferenceLog::class);
    }

    // Scopes
    public function scopeIncoming(Builder $query): Builder
    {
        return $query->where('type', 'incoming');
    }

    public function scopeOutgoing(Builder $query): Builder
    {
        return $query->where('type', 'outgoing');
    }

    public function scopeInternal(Builder $query): Builder
    {
        return $query->where('type', 'internal');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    // Helpers
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'incoming' => 'Surat Masuk',
            'outgoing' => 'Surat Keluar',
            'internal' => 'Surat Internal',
            default    => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'       => 'Draft',
            'pending'     => 'Pending',
            'in_progress' => 'Diproses',
            'completed'   => 'Selesai',
            'archived'    => 'Diarsipkan',
            default       => $this->status,
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'urgent'      => 'Mendesak',
            'very_urgent' => 'Sangat Mendesak',
            default       => 'Biasa',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'yellow',
            'in_progress' => 'blue',
            'completed'   => 'green',
            'archived'    => 'gray',
            default       => 'slate',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent'      => 'orange',
            'very_urgent' => 'red',
            default       => 'green',
        };
    }

    // Generate reference number
    public static function generateReference(string $type): string
    {
        $prefix = match ($type) {
            'incoming' => 'SM',
            'outgoing' => 'SK',
            'internal' => 'SI',
            default    => 'SU',
        };
        $year = now()->year;
        $month = str_pad(now()->month, 2, '0', STR_PAD_LEFT);
        $count = self::where('type', '=', $type, 'and')->whereYear('created_at', '=', $year, 'and')->count() + 1;
        $seq = str_pad($count, 4, '0', STR_PAD_LEFT);
        return "{$prefix}/{$seq}/{$month}/{$year}";
    }
}
