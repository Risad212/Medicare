<?php

namespace App\Modules\Language\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Language\Services\LanguageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageSwitchController extends Controller
{
    public function __invoke(Request $request, string $code, LanguageService $languages): RedirectResponse
    {
        $code = strtolower(trim($code));

        if (! $languages->isActive($code)) {
            abort(404);
        }

        $request->session()->put('locale', $code);

        if ($request->user()) {
            $request->user()->forceFill(['locale' => $code])->save();
        }

        return redirect()->back(fallback: route('home'));
    }
}
