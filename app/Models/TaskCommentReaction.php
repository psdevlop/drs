<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCommentReaction extends Model
{
    protected $fillable = ['task_comment_id', 'user_id', 'emoji'];

    public function comment()
    {
        return $this->belongsTo(TaskComment::class, 'task_comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
