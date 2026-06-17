<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterNumberResetRequest extends Model
{
    protected $fillable = ['requested_by', 'approved_by', 'target_year', 'status', 'notes'];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
