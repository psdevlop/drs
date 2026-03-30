<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class OnCallRotation extends Model
{
    protected $fillable = ['name', 'cycle_type', 'cycle_length', 'start_date', 'end_date', 'is_active', 'notes', 'created_by'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'on_call_rotation_users', 'rotation_id')
            ->withPivot('order')
            ->orderBy('on_call_rotation_users.order')
            ->withTimestamps();
    }

    /**
     * Check if a given date is a holiday based on settings.
     */
    public static function isHoliday($date): bool
    {
        $date = \Carbon\Carbon::parse($date);
        $holidayDays = Setting::getHolidayDays();
        return in_array(strtolower($date->format('l')), $holidayDays);
    }

    /**
     * Get the on-call user(s) for a given date.
     * Working days: all users
     * Holiday days: single rotation user
     */
    public function getUsersForDate($date): Collection
    {
        $date = \Carbon\Carbon::parse($date);

        if ($date->lt($this->start_date)) {
            return collect();
        }
        if ($this->end_date && $date->gt($this->end_date)) {
            return collect();
        }

        $users = $this->users;
        if ($users->isEmpty()) {
            return collect();
        }

        // Holiday: no users assigned
        if (self::isHoliday($date)) {
            return collect();
        }

        // Count working days from start_date to this date
        $workingDayCount = 0;
        $current = $this->start_date->copy();
        while ($current->lt($date)) {
            if (!self::isHoliday($current)) {
                $workingDayCount++;
            }
            $current->addDay();
        }

        // Person In-Charge: one user rotating each working day
        $picIndex = $workingDayCount % $users->count();
        $pic = $users[$picIndex];

        // All users on duty, with PIC marked
        return $users->map(function ($user) use ($pic) {
            $user->is_pic = ($user->id === $pic->id);
            return $user;
        });
    }

    /**
     * @deprecated Use getUsersForDate() instead
     */
    public function getUserForDate($date): ?User
    {
        $users = $this->getUsersForDate($date);
        return $users->first();
    }
}
