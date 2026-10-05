<?php

namespace App\Modules\Language\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Language\Http\Requests\LanguageRequest;
use App\Modules\Language\Models\Language;
use App\Support\AdminNavigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LanguageController extends Controller
{
    public function index(): Response
    {
        $languages = Language::ordered()->paginate(20);

        return Inertia::render('Admin/Languages/Index', [
            'languages' => [
                'data' => $languages->getCollection()->map(fn (Language $language) => [
                    'id' => $language->id,
                    'name' => $language->name,
                    'code' => $language->code,
                    'isDefault' => $language->is_default,
                    'isActive' => $language->is_active,
                    'routes' => [
                        'edit' => route('admin.languages.edit', $language),
                        'default' => route('admin.languages.default', $language),
                        'toggle' => route('admin.languages.toggle', $language),
                        'delete' => route('admin.languages.destroy', $language),
                    ],
                ])->values(),
                'currentPage' => $languages->currentPage(),
                'lastPage' => $languages->lastPage(),
                'total' => $languages->total(),
                'previousPageUrl' => $languages->previousPageUrl(),
                'nextPageUrl' => $languages->nextPageUrl(),
                'pageUrls' => collect(range(1, max(1, $languages->lastPage())))
                    ->map(fn (int $page) => ['number' => $page, 'url' => $languages->url($page)])
                    ->all(),
            ],
            'labels' => $this->labels(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.languages.index'),
                'create' => route('admin.languages.create'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Languages/Form', [
            'mode' => 'create',
            'labels' => $this->labels(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.languages.index'),
                'store' => route('admin.languages.store'),
            ],
        ]);
    }

    public function store(LanguageRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            Language::create($request->validated());
        });

        return redirect()->route('admin.languages.index')->with('success', __('messages.admin.language_created'));
    }

    public function edit(Language $language): Response
    {
        return Inertia::render('Admin/Languages/Form', [
            'mode' => 'edit',
            'language' => [
                'name' => $language->name,
                'code' => $language->code,
                'isActive' => $language->is_active,
            ],
            'labels' => $this->labels(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.languages.index'),
                'update' => route('admin.languages.update', $language),
            ],
        ]);
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

    /**
     * @return array<string, string>
     */
    private function labels(): array
    {
        return [
            'list' => __('messages.admin.language_list'),
            'listSub' => __('messages.admin.language_list_sub'),
            'add' => __('messages.admin.add_language'),
            'edit' => __('messages.admin.edit_language'),
            'name' => __('messages.admin.language_name'),
            'namePlaceholder' => __('messages.admin.language_name_ph'),
            'code' => __('messages.admin.language_code'),
            'codeHint' => __('messages.admin.language_code_hint'),
            'default' => __('messages.admin.default'),
            'setDefault' => __('messages.admin.set_default'),
            'activate' => __('messages.admin.activate'),
            'deactivate' => __('messages.admin.deactivate'),
            'noLanguages' => __('messages.admin.no_languages'),
            'deleteConfirm' => __('messages.admin.delete_confirm'),
            'status' => __('messages.common.status'),
            'active' => __('messages.common.active'),
            'inactive' => __('messages.common.inactive'),
            'actions' => __('messages.common.actions'),
            'delete' => __('messages.common.delete'),
            'save' => __('messages.common.save'),
            'update' => __('messages.common.update'),
            'cancel' => __('messages.common.cancel'),
            'back' => __('messages.common.back'),
        ];
    }
}
