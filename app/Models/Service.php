<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'type',
        'provider',
        'registrant',
        'registrant_id',
        'registration_date',
        'expiration_date',
        'status',
        'notes',
        'url',
        'admin_id',
        'admin_password',
        'test_id',
        'test_password',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'expiration_date' => 'date',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpiringSoon(): bool
    {
        return $this->expiration_date && $this->expiration_date->between(now(), now()->addDays(30));
    }

    public function isExpired(): bool
    {
        return $this->expiration_date && $this->expiration_date->lt(now());
    }
}
