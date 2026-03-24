<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $announcements = $query->orderByDesc('is_pinned')
            ->latest()
            ->paginate(10);

        return view('announcements.index', compact('announcements'));
    }

    public function show(Announcement $announcement)
    {
        $announcement->load('user');
        return view('announcements.show', compact('announcement'));
    }

    public function create()
    {
        return view('announcements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'priority' => ['required', 'in:normal,important,urgent'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $validated['is_pinned'] = $request->boolean('is_pinned');

        $announcement = $request->user()->announcements()->create($validated);

        // Notify all users except the creator
        $users = User::where('id', '!=', $request->user()->id)->get();
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'announcement',
                'title' => __('messages.notif_announcement_title'),
                'message' => __('messages.notif_announcement_message', ['title' => $announcement->title, 'user' => $request->user()->name]),
                'link' => route('announcements.show', $announcement),
            ]);
        }

        return redirect()->route('announcements.index')->with('success', __('messages.announcement_created'));
    }

    public function edit(Announcement $announcement)
    {
        return view('announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'priority' => ['required', 'in:normal,important,urgent'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $validated['is_pinned'] = $request->boolean('is_pinned');

        $announcement->update($validated);

        // Notify all users except the updater
        $users = User::where('id', '!=', $request->user()->id)->get();
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'announcement_updated',
                'title' => __('messages.notif_announcement_updated_title'),
                'message' => __('messages.notif_announcement_updated_message', ['title' => $announcement->title, 'user' => $request->user()->name]),
                'link' => route('announcements.show', $announcement),
            ]);
        }

        return redirect()->route('announcements.show', $announcement)->with('success', __('messages.announcement_updated'));
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('announcements.index')->with('success', __('messages.announcement_deleted'));
    }
}
