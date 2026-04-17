<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['user_id', 'date', 'check_in', 'check_out', 'total_hours', 'notes'];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total_hours' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isCheckedIn(): bool
    {
        return $this->check_in !== null && $this->check_out === null;
    }

    public function isCompleted(): bool
    {
        return $this->check_in !== null && $this->check_out !== null;
    }

    public function calculateHours(): ?float
    {
        if ($this->check_in && $this->check_out) {
            return round($this->check_in->diffInMinutes($this->check_out) / 60, 2);
        }
        return null;
    }

    public function formattedHours(): string
    {
        if ($this->total_hours === null) {
            return '-';
        }
        return self::formatDecimalHours($this->total_hours);
    }

    public static function formatDecimalHours($hours): string
    {
        $h = (int) $hours;
        $m = (int) round(($hours - $h) * 60);
        return $h . 'h ' . $m . 'm';
    }
}
