<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $query = Task::with(['assignees', 'user']);

        if (!$request->user()->isAdmin()) {
            $query->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('assignees', fn ($q2) => $q2->where('users.id', $userId));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->latest()->paginate(15);

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $users = User::where('role', '!=', 'super_admin')->orderBy('name')->get();
        return view('tasks.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'expected_end_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'array'],
            'assigned_to.*' => ['exists:users,id'],
            'requested_by' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'priority' => ['required', 'in:low,medium,high'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $assignedUsers = $validated['assigned_to'] ?? [];
        unset($validated['assigned_to']);

        $task = $request->user()->tasks()->create($validated);

        if (!empty($assignedUsers)) {
            $task->assignees()->sync($assignedUsers);
        }

        $this->handleAttachments($request, $task);

        // Notify assigned users
        foreach ($assignedUsers as $assigneeId) {
            if ((int) $assigneeId !== $request->user()->id) {
                Notification::create([
                    'user_id' => $assigneeId,
                    'type' => 'task_assigned',
                    'title' => __('messages.notif_task_assigned_title'),
                    'message' => __('messages.notif_task_assigned_message', ['task' => $task->title, 'user' => $request->user()->name]),
                    'link' => route('tasks.show', $task),
                ]);
            }
        }

        // Notify admins about new task
        $notifiedIds = array_merge($assignedUsers, [$request->user()->id]);
        $admins = User::where('role', '!=', 'user')->whereNotIn('id', $notifiedIds)->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'task_created',
                'title' => __('messages.notif_task_created_title'),
                'message' => __('messages.notif_task_created_message', ['task' => $task->title, 'user' => $request->user()->name]),
                'link' => route('tasks.show', $task),
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $this->authorizeTask($task);
        $task->load(['user', 'assignees', 'requester', 'attachments', 'comments.user']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorizeTask($task);
        $task->load(['attachments', 'assignees']);
        $users = User::where('role', '!=', 'super_admin')->orderBy('name')->get();
        return view('tasks.edit', compact('task', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTask($task);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'expected_end_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'array'],
            'assigned_to.*' => ['exists:users,id'],
            'requested_by' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'priority' => ['required', 'in:low,medium,high'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $assignedUsers = $validated['assigned_to'] ?? [];
        unset($validated['assigned_to']);

        if ($validated['status'] === 'completed') {
            $validated['progress'] = 100;
        } elseif ($validated['status'] !== 'completed' && $validated['progress'] === 100) {
            $validated['progress'] = 90;
        }

        $task->update($validated);
        $task->assignees()->sync($assignedUsers);

        $this->handleAttachments($request, $task);

        // Notify task owner if updated by someone else
        if ($task->user_id !== $request->user()->id) {
            Notification::create([
                'user_id' => $task->user_id,
                'type' => 'task_updated',
                'title' => __('messages.notif_task_updated_title'),
                'message' => __('messages.notif_task_updated_message', ['task' => $task->title, 'user' => $request->user()->name]),
                'link' => route('tasks.show', $task),
            ]);
        }

        // Notify assigned users if updated by someone else
        foreach ($assignedUsers as $assigneeId) {
            if ((int) $assigneeId !== $request->user()->id && (int) $assigneeId !== $task->user_id) {
                Notification::create([
                    'user_id' => $assigneeId,
                    'type' => 'task_updated',
                    'title' => __('messages.notif_task_updated_title'),
                    'message' => __('messages.notif_task_updated_message', ['task' => $task->title, 'user' => $request->user()->name]),
                    'link' => route('tasks.show', $task),
                ]);
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorizeTask($task);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed'],
        ]);

        if ($validated['status'] === 'completed') {
            $validated['progress'] = 100;
        } elseif ($task->progress === 100) {
            $validated['progress'] = 90;
        }

        $task->update($validated);

        // Notify task owner if status changed by someone else
        if ($task->user_id !== auth()->id()) {
            Notification::create([
                'user_id' => $task->user_id,
                'type' => 'task_updated',
                'title' => __('messages.notif_task_updated_title'),
                'message' => __('messages.notif_task_status_changed_message', ['task' => $task->title, 'user' => auth()->user()->name, 'status' => $validated['status']]),
                'link' => route('tasks.show', $task),
            ]);
        }

        // Notify assigned users if status changed by someone else
        $task->load('assignees');
        foreach ($task->assignees as $assignee) {
            if ($assignee->id !== auth()->id() && $assignee->id !== $task->user_id) {
                Notification::create([
                    'user_id' => $assignee->id,
                    'type' => 'task_updated',
                    'title' => __('messages.notif_task_updated_title'),
                    'message' => __('messages.notif_task_status_changed_message', ['task' => $task->title, 'user' => auth()->user()->name, 'status' => $validated['status']]),
                    'link' => route('tasks.show', $task),
                ]);
            }
        }

        return redirect()->back()->with('success', __('messages.task_updated'));
    }

    public function destroy(Task $task)
    {
        $this->authorizeTask($task);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    public function destroyAttachment(TaskAttachment $attachment)
    {
        $this->authorizeTask($attachment->task);

        if ($attachment->file_path) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return redirect()->back()->with('success', __('messages.attachment_deleted'));
    }

    public function storeComment(Request $request, Task $task)
    {
        $this->authorizeTask($task);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        $commentData = [
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $commentData['attachment_path'] = $file->store('comment-attachments', 'public');
            $commentData['attachment_name'] = $file->getClientOriginalName();
        }

        $comment = $task->comments()->create($commentData);

        // Notify task owner
        if ($task->user_id !== auth()->id()) {
            Notification::create([
                'user_id' => $task->user_id,
                'type' => 'task_comment',
                'title' => __('messages.notif_task_comment_title'),
                'message' => __('messages.notif_task_comment_message', ['task' => $task->title, 'user' => auth()->user()->name]),
                'link' => route('tasks.show', $task),
            ]);
        }

        // Notify assigned users
        foreach ($task->assignees as $assignee) {
            if ($assignee->id !== auth()->id() && $assignee->id !== $task->user_id) {
                Notification::create([
                    'user_id' => $assignee->id,
                    'type' => 'task_comment',
                    'title' => __('messages.notif_task_comment_title'),
                    'message' => __('messages.notif_task_comment_message', ['task' => $task->title, 'user' => auth()->user()->name]),
                    'link' => route('tasks.show', $task),
                ]);
            }
        }

        return redirect()->route('tasks.show', $task)->with('success', __('messages.comment_added'));
    }

    public function destroyComment(Task $task, TaskComment $comment)
    {
        $this->authorizeTask($task);

        if ($comment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($comment->attachment_path) {
            Storage::disk('public')->delete($comment->attachment_path);
        }

        $comment->delete();

        return redirect()->route('tasks.show', $task)->with('success', __('messages.comment_deleted'));
    }

    private function handleAttachments(Request $request, Task $task): void
    {
        $request->validate([
            'files.*' => ['nullable', 'file', 'max:10240'],
            'images.*' => ['nullable', 'image', 'max:5120'],
            'links' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('task-attachments/files', 'public');
                $task->attachments()->create([
                    'type' => 'file',
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('task-attachments/images', 'public');
                $task->attachments()->create([
                    'type' => 'image',
                    'file_path' => $path,
                    'original_name' => $image->getClientOriginalName(),
                ]);
            }
        }

        if ($request->filled('links')) {
            $links = array_filter(array_map('trim', explode("\n", $request->links)));
            foreach ($links as $link) {
                if (filter_var($link, FILTER_VALIDATE_URL)) {
                    $task->attachments()->create([
                        'type' => 'link',
                        'url' => $link,
                    ]);
                }
            }
        }
    }

    private function authorizeTask(Task $task): void
    {
        if (auth()->user()->isAdmin()) {
            return;
        }
        $userId = auth()->id();
        if ($task->user_id !== $userId && !$task->assignees()->where('users.id', $userId)->exists()) {
            abort(403);
        }
    }
}
