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

        return view('oncall.index', compact('onCalls'));
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
        $oncall->load('users');
        $users = User::orderBy('name')->get();
        $selectedUsers = $oncall->users->pluck('id')->toArray();

        return view('oncall.edit', compact('oncall', 'users', 'selectedUsers'));
    }

    public function update(Request $request, OnCall $oncall)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

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
