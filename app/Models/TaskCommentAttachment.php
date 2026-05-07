<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCommentAttachment extends Model
{
    protected $fillable = ['task_comment_id', 'file_path', 'original_name'];

    public function comment()
    {
        return $this->belongsTo(TaskComment::class, 'task_comment_id');
    }
}
