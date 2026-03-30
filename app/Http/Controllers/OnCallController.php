<?php

namespace App\Http\Controllers;

use App\Models\OnCall;
use App\Models\OnCallRotation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OnCallController extends Controller
{
    public function index(Request $request)
    {
        $rotations = OnCallRotation::with(['users', 'creator'])->latest()->get();
        $activeRotations = $rotations->where('is_active', true);

        $today = now()->startOfDay();
        $startDate = Carbon::parse($request->get('start_date', $today->format('Y-m-d')));
        $days = min((int) $request->get('days', 7), 90);

        // Build schedule dynamically from active rotations
        $schedule = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dayData = ['date' => $date, 'users' => [], 'pic' => null, 'is_holiday' => OnCallRotation::isHoliday($date)];

            foreach ($activeRotations as $rotation) {
                $dutyUsers = $rotation->getUsersForDate($date);
                foreach ($dutyUsers as $user) {
                    $dayData['users'][] = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'is_pic' => $user->is_pic ?? false,
                    ];
                    if ($user->is_pic ?? false) {
                        $dayData['pic'] = $user->name;
                    }
                }
            }

            // Set auto PIC id from rotation
            foreach ($dayData['users'] as $u) {
                if ($u['is_pic']) {
                    $dayData['pic_user_id'] = $u['id'];
                }
            }

            // Check for manual PIC override from on_calls table
            $manualEntry = OnCall::with('pic')->where('date', $date->format('Y-m-d'))->first();
            if ($manualEntry && $manualEntry->pic_user_id) {
                $dayData['pic'] = $manualEntry->pic->name;
                $dayData['pic_user_id'] = $manualEntry->pic_user_id;
                $dayData['oncall_id'] = $manualEntry->id;
            } elseif ($manualEntry) {
                $dayData['oncall_id'] = $manualEntry->id;
            }

            $schedule[] = $dayData;
        }

        return view('oncall.index', compact('rotations', 'schedule', 'today', 'startDate', 'days'));
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
            'pic_user_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

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
            'pic_user_id' => $validated['pic_user_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $onCall->users()->attach($validated['users']);

        return redirect()->route('oncall.index')->with('success', __('messages.oncall_created'));
    }

    public function edit(OnCall $oncall)
    {
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
        if ($oncall->date->lt(now()->startOfDay())) {
            return redirect()->route('oncall.index')->with('error', __('messages.oncall_past_no_edit'));
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['exists:users,id'],
            'pic_user_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

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
            'pic_user_id' => $validated['pic_user_id'] ?? null,
        ]);

        $oncall->users()->sync($validated['users']);

        return redirect()->route('oncall.index')->with('success', __('messages.oncall_updated'));
    }

    public function updatePicByDate(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'pic_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $onCall = OnCall::firstOrCreate(
            ['date' => $validated['date']],
            ['created_by' => auth()->id()]
        );

        $onCall->update(['pic_user_id' => $validated['pic_user_id'] ?: null]);

        return redirect()->back()->with('success', __('messages.pic_updated'));
    }

    public function updatePic(Request $request, OnCall $oncall)
    {
        $validated = $request->validate([
            'pic_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $oncall->update(['pic_user_id' => $validated['pic_user_id'] ?: null]);

        return redirect()->back()->with('success', __('messages.pic_updated'));
    }

    public function destroy(OnCall $oncall)
    {
        $oncall->delete();

        return redirect()->route('oncall.index')->with('success', __('messages.oncall_deleted'));
    }

    // ---- Rotation Management ----

    public function rotations()
    {
        $rotations = OnCallRotation::with(['users', 'creator'])->latest()->paginate(15);
        return view('oncall.rotations.index', compact('rotations'));
    }

    public function createRotation()
    {
        $users = User::orderBy('name')->get();
        return view('oncall.rotations.create', compact('users'));
    }

    public function storeRotation(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cycle_type' => ['required', 'in:daily,weekly'],
            'cycle_length' => ['required', 'integer', 'min:1', 'max:30'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'users' => ['required', 'array', 'min:2'],
            'users.*' => ['exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Check for overlapping rotation schedules
        $overlap = OnCallRotation::where(function ($q) use ($validated) {
            $q->where(function ($q2) use ($validated) {
                $q2->where('start_date', '<=', $validated['end_date'] ?? '9999-12-31')
                    ->where(function ($q3) use ($validated) {
                        $q3->whereNull('end_date')
                            ->orWhere('end_date', '>=', $validated['start_date']);
                    });
            });
        })->first();

        if ($overlap) {
            return redirect()->back()->withInput()->with('error',
                __('messages.rotation_overlap', ['name' => $overlap->name])
            );
        }

        $rotation = OnCallRotation::create([
            'name' => $validated['name'],
            'cycle_type' => $validated['cycle_type'],
            'cycle_length' => $validated['cycle_length'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        foreach ($validated['users'] as $order => $userId) {
            $rotation->users()->attach($userId, ['order' => $order]);
        }

        return redirect()->route('oncall.index')->with('success', __('messages.rotation_created'));
    }

    public function editRotation(OnCallRotation $rotation)
    {
        $rotation->load('users');
        $users = User::orderBy('name')->get();
        return view('oncall.rotations.edit', compact('rotation', 'users'));
    }

    public function updateRotation(Request $request, OnCallRotation $rotation)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cycle_type' => ['required', 'in:daily,weekly'],
            'cycle_length' => ['required', 'integer', 'min:1', 'max:30'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'users' => ['required', 'array', 'min:2'],
            'users.*' => ['exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable'],
        ]);

        // Check for overlapping rotation schedules (excluding current)
        $overlap = OnCallRotation::where('id', '!=', $rotation->id)
            ->where(function ($q) use ($validated) {
                $q->where('start_date', '<=', $validated['end_date'] ?? '9999-12-31')
                    ->where(function ($q2) use ($validated) {
                        $q2->whereNull('end_date')
                            ->orWhere('end_date', '>=', $validated['start_date']);
                    });
            })->first();

        if ($overlap) {
            return redirect()->back()->withInput()->with('error',
                __('messages.rotation_overlap', ['name' => $overlap->name])
            );
        }

        $rotation->update([
            'name' => $validated['name'],
            'cycle_type' => $validated['cycle_type'],
            'cycle_length' => $validated['cycle_length'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        $rotation->users()->detach();
        foreach ($validated['users'] as $order => $userId) {
            $rotation->users()->attach($userId, ['order' => $order]);
        }

        return redirect()->route('oncall.index')->with('success', __('messages.rotation_updated'));
    }

    public function destroyRotation(OnCallRotation $rotation)
    {
        // Delete on-call entries generated from this rotation
        OnCall::where('notes', 'LIKE', '%' . $rotation->name . '%')->delete();

        $rotation->delete();
        return redirect()->route('oncall.index')->with('success', __('messages.rotation_deleted'));
    }

    public function generateFromRotations(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        if ($endDate->diffInDays($startDate) > 90) {
            return redirect()->back()->with('error', __('messages.rotation_max_days'));
        }

        $rotations = OnCallRotation::with('users')->where('is_active', true)->get();

        if ($rotations->isEmpty()) {
            return redirect()->back()->with('error', __('messages.rotation_none_active'));
        }

        $generated = 0;
        $skipped = 0;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            foreach ($rotations as $rotation) {
                $dutyUsers = $rotation->getUsersForDate($date);
                if ($dutyUsers->isEmpty()) {
                    continue;
                }

                $userIds = $dutyUsers->pluck('id')->toArray();

                // Check which users already have an on-call entry for this date
                $existingUserIds = OnCall::where('date', $date->format('Y-m-d'))
                    ->whereHas('users', fn ($q) => $q->whereIn('users.id', $userIds))
                    ->with('users')
                    ->get()
                    ->flatMap(fn ($oc) => $oc->users->pluck('id'))
                    ->toArray();

                $newUserIds = array_diff($userIds, $existingUserIds);

                if (empty($newUserIds)) {
                    $skipped++;
                    continue;
                }

                $picUser = $dutyUsers->first(fn ($u) => $u->is_pic ?? false);

                $onCall = OnCall::create([
                    'date' => $date->format('Y-m-d'),
                    'notes' => __('messages.rotation_auto_note', ['rotation' => $rotation->name]),
                    'pic_user_id' => $picUser?->id,
                    'created_by' => auth()->id(),
                ]);

                $onCall->users()->attach($newUserIds);
                $generated++;
                if (count($existingUserIds)) {
                    $skipped++;
                }
            }
        }

        return redirect()->route('oncall.index')->with('success',
            __('messages.rotation_generated', ['count' => $generated, 'skipped' => $skipped])
        );
    }

    public function previewRotation(Request $request)
    {
        $rotations = OnCallRotation::with('users')->where('is_active', true)->get();
        $startDate = Carbon::parse($request->get('start_date', now()->format('Y-m-d')));
        $days = min((int) $request->get('days', 7), 90);

        $preview = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dayData = ['date' => $date, 'users' => []];

            foreach ($rotations as $rotation) {
                $dutyUsers = $rotation->getUsersForDate($date);
                foreach ($dutyUsers as $user) {
                    $dayData['users'][] = [
                        'name' => $user->name,
                        'rotation' => $rotation->name,
                        'is_pic' => $user->is_pic ?? false,
                    ];
                }
            }

            $preview[] = $dayData;
        }

        $users = User::orderBy('name')->get();

        return view('oncall.rotations.preview', compact('preview', 'rotations', 'startDate', 'days', 'users'));
    }
}
