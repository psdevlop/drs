<?php

namespace App\Http\Controllers;

use App\Models\OnCall;
use App\Models\User;
use Illuminate\Http\Request;

class OnCallController extends Controller
{
    public function index()
    {
        $onCalls = OnCall::with(['users', 'creator'])
            ->orderBy('date', 'desc')
            ->paginate(15);

        $today = now()->startOfDay();

        return view('oncall.index', compact('onCalls', 'today'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('oncall.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Check if any selected user is already on call for this date
        $existingOnCalls = OnCall::where('date', $validated['date'])
            ->whereHas('users', function ($q) use ($validated) {
                $q->whereIn('users.id', $validated['users']);
            })
            ->with('users')
            ->get();

        if ($existingOnCalls->isNotEmpty()) {
            $duplicateUsers = [];
            foreach ($existingOnCalls as $existing) {
                foreach ($existing->users as $user) {
                    if (in_array($user->id, $validated['users'])) {
                        $duplicateUsers[] = $user->name;
                    }
                }
            }
            $duplicateUsers = array_unique($duplicateUsers);

            return redirect()->back()->withInput()->with('error',
                __('messages.oncall_duplicate_users', ['users' => implode(', ', $duplicateUsers), 'date' => $validated['date']])
            );
        }

        $onCall = OnCall::create([
            'date' => $validated['date'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $onCall->users()->attach($validated['users']);

        return redirect()->route('oncall.index')->with('success', __('messages.oncall_created'));
    }

    public function edit(OnCall $oncall)
    {
        // Prevent editing if date has passed
        if ($oncall->date->lt(now()->startOfDay())) {
            return redirect()->route('oncall.index')->with('error', __('messages.oncall_past_no_edit'));
        }

        $oncall->load('users');
        $users = User::orderBy('name')->get();
        $selectedUsers = $oncall->users->pluck('id')->toArray();

        return view('oncall.edit', compact('oncall', 'users', 'selectedUsers'));
    }

    public function update(Request $request, OnCall $oncall)
    {
        // Prevent updating if date has passed
        if ($oncall->date->lt(now()->startOfDay())) {
            return redirect()->route('oncall.index')->with('error', __('messages.oncall_past_no_edit'));
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Check if any selected user is already on call for this date (excluding current record)
        $existingOnCalls = OnCall::where('date', $validated['date'])
            ->where('id', '!=', $oncall->id)
            ->whereHas('users', function ($q) use ($validated) {
                $q->whereIn('users.id', $validated['users']);
            })
            ->with('users')
            ->get();

        if ($existingOnCalls->isNotEmpty()) {
            $duplicateUsers = [];
            foreach ($existingOnCalls as $existing) {
                foreach ($existing->users as $user) {
                    if (in_array($user->id, $validated['users'])) {
                        $duplicateUsers[] = $user->name;
                    }
                }
            }
            $duplicateUsers = array_unique($duplicateUsers);

            return redirect()->back()->withInput()->with('error',
                __('messages.oncall_duplicate_users', ['users' => implode(', ', $duplicateUsers), 'date' => $validated['date']])
            );
        }

        $oncall->update([
            'date' => $validated['date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $oncall->users()->sync($validated['users']);

        return redirect()->route('oncall.index')->with('success', __('messages.oncall_updated'));
    }

    public function destroy(OnCall $oncall)
    {
        $oncall->delete();

        return redirect()->route('oncall.index')->with('success', __('messages.oncall_deleted'));
    }
}
