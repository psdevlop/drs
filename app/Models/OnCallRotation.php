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
        static $holidayDays = null;
        static $holidayDates = null;
        if ($holidayDays === null) {
            $holidayDays = Setting::getHolidayDays();
            // Use schedule-filtered list so generic "Public Holiday" entries
            // (the Nepali source's weekly pattern) don't disable the on-duty rotation.
            $holidayDates = array_flip(array_column(Setting::getScheduleHolidayDates(), 'date'));
        }
        $date = \Carbon\Carbon::parse($date);
        if (in_array(strtolower($date->format('l')), $holidayDays, true)) {
            return true;
        }
        return isset($holidayDates[$date->format('Y-m-d')]);
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

    /**
     * Pick the rotating on-call user for an off-day (Saturday or government holiday).
     * Rotates one user per off-day across the rotation user list.
     */
    public function getOnCallUserForDate($date): ?User
    {
        $date = \Carbon\Carbon::parse($date);

        if ($date->lt($this->start_date)) {
            return null;
        }
        if ($this->end_date && $date->gt($this->end_date)) {
            return null;
        }

        $users = $this->users;
        if ($users->isEmpty()) {
            return null;
        }

        $holidayDates = array_flip(array_column(Setting::getScheduleHolidayDates(), 'date'));

        $offDayCount = 0;
        $current = $this->start_date->copy();
        while ($current->lte($date)) {
            $isSat = $current->dayOfWeek === \Carbon\Carbon::SATURDAY;
            $isHoliday = isset($holidayDates[$current->format('Y-m-d')]);
            if ($isSat || $isHoliday) {
                $offDayCount++;
            }
            $current->addDay();
        }

        if ($offDayCount === 0) {
            return null;
        }

        $index = ($offDayCount - 1) % $users->count();
        return $users[$index];
    }
}
