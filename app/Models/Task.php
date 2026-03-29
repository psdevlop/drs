<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'requested_by',
        'title',
        'description',
        'expected_end_date',
        'status',
        'progress',
        'priority',
        'start_date',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'expected_end_date' => 'date',
            'start_date' => 'date',
            'due_date' => 'date',
            'progress' => 'integer',
        ];
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = $value ? strip_tags($value, '<p><br><strong><em><u><s><ul><ol><li><a><img><blockquote><table><thead><tbody><tr><td><th><h2><h3><h4><figure><figcaption>') : $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->latest();
    }
}
