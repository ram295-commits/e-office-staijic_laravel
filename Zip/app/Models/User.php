<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'nip', 'email', 'password',
        'department', 'position', 'role', 'is_active', 'avatar', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // Relationships
    public function createdMails(): HasMany
    {
        return $this->hasMany(Mail::class, 'created_by');
    }

    public function assignedMails(): HasMany
    {
        return $this->hasMany(Mail::class, 'assigned_to');
    }

    public function sentDispositions(): HasMany
    {
        return $this->hasMany(Disposition::class, 'from_user_id');
    }

    public function receivedDispositions(): HasMany
    {
        return $this->hasMany(Disposition::class, 'to_user_id');
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class)->withTimestamps();
    }

    /**
     * Check if the user is authorized to generate a document of the given DocumentType ID.
     */
    public function canUseDocumentType(int $documentTypeId): bool
    {
        if ($this->isAdmin()) {
            return true; // Admins have global access
        }
        $userUnitIds = $this->units()->pluck('units.id');
        $docType = \App\Models\DocumentType::whereKey($documentTypeId)->first();
        return $docType && $userUnitIds->contains($docType->unit_id);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin'   => 'Administrator',
            'manager' => 'Manager',
            default   => 'Staff',
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        $initial = strtoupper(substr($this->name, 0, 1));
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&background=4f46e5&color=fff&size=128";
    }
}
