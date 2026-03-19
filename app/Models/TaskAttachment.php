<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    protected $fillable = [
        'task_id',
        'type',
        'file_path',
        'original_name',
        'url',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
