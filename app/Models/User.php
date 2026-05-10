<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'role',
        'intern_role',
        'team_role',
        'last_seen_at',
        'typing_to',
        'typing_at',
    ];

    public const INTERN_ROLES = [
        'senior_programmer' => 'Senior Programmer',
        'mid_programmer' => 'Mid-level Programmer',
        'translator' => 'Translator',
    ];

    public const TEAM_ROLES = [
        'director' => 'Director',
        'team_manager' => 'Team Manager',
        'team_member' => 'Team Member',
    ];

    public function internRoleLabel(): ?string
    {
        return self::INTERN_ROLES[$this->intern_role] ?? null;
    }

    public function teamRoleLabel(): ?string
    {
        return self::TEAM_ROLES[$this->team_role] ?? null;
    }

    public function isDirector(): bool
    {
        return $this->team_role === 'director';
    }

    public function isTeamManager(): bool
    {
        return $this->team_role === 'team_manager';
    }

    public function isTeamMember(): bool
    {
        return $this->team_role === 'team_member';
    }

    public function evaluationsGiven()
    {
        return $this->hasMany(Evaluation::class, 'evaluator_id');
    }

    public function evaluationsReceived()
    {
        return $this->hasMany(Evaluation::class, 'evaluee_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function assignedTasks()
    {
        return $this->belongsToMany(Task::class, 'task_user')->withTimestamps();
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
            'typing_at' => 'datetime',
        ];
    }
}
