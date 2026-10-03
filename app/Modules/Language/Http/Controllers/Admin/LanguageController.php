<?php

namespace App\Modules\Language\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Language\Http\Requests\LanguageRequest;
use App\Modules\Language\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function index(): View
    {
        return view('languages.index', [
            'languages' => Language::ordered()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('languages.create');
    }

    public function store(LanguageRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            Language::create($request->validated());
        });

        return redirect()->route('admin.languages.index')->with('success', __('messages.admin.language_created'));
    }

    public function edit(Language $language): View
    {
        return view('languages.edit', compact('language'));
    }

    public function update(LanguageRequest $request, Language $language): RedirectResponse
    {
        DB::transaction(function () use ($request, $language) {
            $language->update($request->validated());
        });

        return redirect()->route('admin.languages.index')->with('success', __('messages.admin.language_updated'));
    }

    public function destroy(Language $language): RedirectResponse
    {
        if ($language->is_default) {
            return back()->with('error', __('messages.admin.language_default_locked'));
        }

        $language->delete();

        return redirect()->route('admin.languages.index')->with('success', __('messages.admin.language_deleted'));
    }

    public function setDefault(Language $language): RedirectResponse
    {
        abort_unless($language->is_active, 422, __('messages.admin.language_must_be_active'));

        DB::transaction(function () use ($language) {
            Language::where('is_default', true)->update(['is_default' => false]);
            $language->update(['is_default' => true]);
        });

        return back()->with('success', __('messages.admin.language_updated'));
    }

    public function toggle(Language $language): RedirectResponse
    {
        if ($language->is_default && $language->is_active) {
            return back()->with('error', __('messages.admin.language_default_locked'));
        }

        $language->update(['is_active' => ! $language->is_active]);

        return back()->with('success', __('messages.admin.language_updated'));
    }
}
