<?php

namespace App\Http\Middleware;

use App\Modules\Language\Services\LanguageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function rootView(Request $request): string
    {
        return $request->routeIs(
            'home',
            'about',
            'service',
            'service.show',
            'doctor',
            'doctor.show',
            'blog',
            'blog.show',
            'contact',
            'appointment',
            'appointment.cancel-page',
        ) ? 'frontend-app' : $this->rootView;
    }

    public function share(Request $request): array
    {
        $sharedViews = view()->getShared();
        $setting = $sharedViews['setting'] ?? null;
        $footerServices = $sharedViews['footerServices'] ?? collect();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn () => $request->user()?->only('id', 'name', 'email', 'role'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'seoSuccess' => fn () => $request->session()->get('seo_success'),
                'seoError' => fn () => $request->session()->get('seo_error'),
            ],
            'csrfToken' => fn () => csrf_token(),
            'site' => [
                'name' => $setting?->site_name ?: config('app.name', 'MediCare'),
                'logo' => $setting?->logo,
                'footerLogo' => $setting?->footer_logo,
                'footerDescription' => $setting?->footer_description,
                'address' => $setting?->address,
                'workingHours' => $setting?->working_hours,
                'phone' => $setting?->phone,
                'email' => $setting?->email,
                'facebook' => $setting?->facebook,
                'twitter' => $setting?->twitter,
                'linkedin' => $setting?->linkedin,
                'youtube' => $setting?->youtube,
                'mapEmbedUrl' => $setting?->map_embed_url,
                'copyright' => $setting?->copyright,
            ],
            'footerServices' => $footerServices->map(fn ($service) => [
                'title' => $service->title,
                'slug' => $service->slug,
            ])->values(),
            'navLabels' => fn () => [
                'home' => __('messages.nav.home'),
                'about' => __('messages.nav.about'),
                'services' => __('messages.nav.services'),
                'doctors' => __('messages.nav.doctors'),
                'blog' => __('messages.nav.blog'),
                'contact' => __('messages.nav.contact'),
                'appointment' => __('messages.nav.appointment'),
                'language' => __('messages.nav.language'),
                'login' => __('messages.nav.login'),
                'profile' => __('messages.nav.profile'),
            ],
            'availableLanguages' => Route::has('language.switch')
                ? app(LanguageService::class)->available()
                : [['name' => 'English', 'code' => 'en']],
            'frontendRoutes' => [
                'home' => route('home'),
                'about' => route('about'),
                'services' => route('service'),
                'doctors' => route('doctor'),
                'blog' => route('blog'),
                'contact' => route('contact'),
                'appointment' => route('appointment'),
                'contactSubmit' => route('contact.submit'),
                'appointmentStore' => route('appointment.store'),
                'availableSlots' => route('get.slots'),
                'login' => route('login'),
                'profile' => Route::has('profile') ? route('profile') : null,
                'notifications' => Route::has('notifications.index') ? route('notifications.index') : null,
                'doctorDashboard' => Route::has('doctor.dashboard') ? route('doctor.dashboard') : null,
                'adminDashboard' => Route::has('admin.home') ? route('admin.home') : null,
                'logout' => Route::has('logout') ? route('logout') : null,
            ],
        ];
    }
}
