<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailContentLog extends Model
{
    protected $fillable = [
        'mail_id',
        'changed_by',
        'action',
        'changes',
        'old_values',
    ];

    protected function casts(): array
    {
        return [
            'changes'    => 'array',
            'old_values' => 'array',
        ];
    }

    public function mail(): BelongsTo
    {
        return $this->belongsTo(Mail::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
