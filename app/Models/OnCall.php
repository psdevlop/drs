<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnCall extends Model
{
    protected $fillable = ['date', 'notes', 'created_by'];

    protected $casts = [
        'date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'on_call_users')->withTimestamps();
    }
}
