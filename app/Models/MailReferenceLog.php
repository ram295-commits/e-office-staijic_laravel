<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailReferenceLog extends Model
{
    protected $fillable = [
        'mail_id',
        'changed_by',
        'old_reference',
        'new_reference',
        'reason',
    ];

    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
