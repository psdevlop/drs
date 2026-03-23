<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with('creator')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('registrant', 'like', "%{$search}%")
                  ->orWhere('registrant_id', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%");
            });
        }

        $services = $query->paginate(15);

        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:domain,hosting,cdn,website'],
            'provider' => ['nullable', 'string', 'max:255'],
            'registrant' => ['nullable', 'string', 'max:255'],
            'registrant_id' => ['nullable', 'string', 'max:255'],
            'registration_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,expired,pending,suspended'],
            'notes' => ['nullable', 'string'],
            'url' => ['nullable', 'string', 'max:255'],
            'admin_id' => ['nullable', 'string', 'max:255'],
            'admin_password' => ['nullable', 'string', 'max:255'],
            'test_id' => ['nullable', 'string', 'max:255'],
            'test_password' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['created_by'] = auth()->id();

        Service::create($validated);

        return redirect()->route('services.index')->with('success', __('messages.service_created'));
    }

    public function show(Service $service)
    {
        $service->load('creator');
        return view('services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:domain,hosting,cdn,website'],
            'provider' => ['nullable', 'string', 'max:255'],
            'registrant' => ['nullable', 'string', 'max:255'],
            'registrant_id' => ['nullable', 'string', 'max:255'],
            'registration_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,expired,pending,suspended'],
            'notes' => ['nullable', 'string'],
            'url' => ['nullable', 'string', 'max:255'],
        ]);

        $service->update($validated);

        return redirect()->route('services.index')->with('success', __('messages.service_updated'));
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('services.index')->with('success', __('messages.service_deleted'));
    }
}
