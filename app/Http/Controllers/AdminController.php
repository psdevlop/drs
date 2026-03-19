<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_reports_today' => DailyReport::where('report_date', today())->count(),
            'total_reports' => DailyReport::count(),
        ];

        $recentReports = DailyReport::with('user')->latest('report_date')->take(10)->get();
        $users = User::withCount(['tasks', 'dailyReports'])->get();

        return view('admin.dashboard', compact('stats', 'recentReports', 'users'));
    }

    public function userReports(User $user)
    {
        $reports = $user->dailyReports()->latest('report_date')->paginate(15);
        return view('admin.user-reports', compact('user', 'reports'));
    }

    public function showReport(DailyReport $report)
    {
        $report->load('user');
        return view('admin.show-report', compact('report'));
    }

    public function users()
    {
        $users = User::withCount(['tasks', 'dailyReports'])->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        return view('admin.users.create');
    }

    public function storeUser(Request $request)
    {
        $allowedRoles = auth()->user()->isSuperAdmin() ? 'user,admin,super_admin' : 'user';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'in:' . $allowedRoles],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', __('messages.user_created'));
    }

    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $allowedRoles = auth()->user()->isSuperAdmin() ? 'user,admin,super_admin' : 'user';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:' . $allowedRoles],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', __('messages.user_updated'));
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', __('messages.cannot_delete_self'));
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', __('messages.user_deleted'));
    }
}
