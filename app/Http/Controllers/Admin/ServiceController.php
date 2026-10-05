<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $services = Service::orderBy('order')->latest()->get();

        return Inertia::render('Admin/Services/Index', [
            'services' => $services->map(fn (Service $service) => [
                'id' => $service->id,
                'title' => $service->title,
                'description' => $service->description,
                'icon' => $service->icon,
                'order' => $service->order,
                'status' => (int) $service->status,
            ])->values(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.services.index'),
                'create' => route('admin.services.create'),
                'settings' => route('settings.service'),
                'editBase' => url('/admin/services'),
                'deleteBase' => url('/admin/services'),
            ],
            'storageUrl' => asset('storage'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Services/Create', [
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.services.index'),
                'store' => route('admin.services.store'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'icon' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ]);

        $data = $validated;
        $data['status'] = $request->has('status') ? 1 : 0;

        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('services', 'public');
        }

        Service::create($data);

        return back()->with('success', 'Service added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service): Response
    {
        return Inertia::render('Admin/Services/Edit', [
            'service' => [
                'id' => $service->id,
                'title' => $service->title,
                'description' => $service->description,
                'icon' => $service->icon,
                'buttonText' => $service->button_text,
                'buttonUrl' => $service->button_url,
                'order' => $service->order,
                'status' => (bool) $service->status,
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.services.index'),
                'update' => route('admin.services.update', $service),
            ],
            'storageUrl' => asset('storage'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'icon' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ]);

        $data = $validated;
        $data['status'] = $request->has('status') ? 1 : 0;

        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('services', 'public');
        }

        $service->update($data);

        return back()->with('success', 'Service updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete();

        return back()->with('success', 'Service deleted successfully!');
    }
}
