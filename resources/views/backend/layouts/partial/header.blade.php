{{-- -----------------------------------------------------------------
     MediCare backend shell: white sidebar + white topbar + content.
     Bootstrap 5 + admin-brand.css. Hooks kept for main.js:
       - data-toggle="sidebar"  → toggles body.sidenav-toggled
       - data-toggle="treeview" → toggles .is-expanded on parent <li>
     The avatar menu is a native Bootstrap dropdown (no custom JS).
   ----------------------------------------------------------------- --}}

@php
    $me = auth()->user();
    $isDoctor = $me && $me->role === 'doctor';
    $isAdmin = $me && $me->role === 'admin';
    $isRecep = $me && $me->role === 'receptionist';
    $isLabTech = $me && $me->role === 'lab-technician';
    $isPharm = $me && $me->role === 'pharmacist';
    $navCounts = Cache::remember('mc.nav.counts', 60, fn () => [
        'pendingAppointments' => App\Models\Appointment::where('status', 0)->count(),
        'pendingComments' => App\Models\BlogComment::where('status', 0)->count(),
        'todayAppointments' => App\Models\Appointment::whereDate('appointment_date', now()->toDateString())->count(),
    ]);
@endphp

{{-- ============ Topbar ============ --}}
<header class="admin-topbar">
    <button type="button" data-toggle="sidebar" class="top-iconbtn d-lg-none" aria-label="Toggle Sidebar">
        <i class="bi bi-list"></i>
    </button>
    <button type="button" data-toggle="sidebar" class="top-iconbtn d-none d-lg-inline-flex ms-n2" aria-label="Collapse Sidebar">
        <i class="bi bi-list"></i>
    </button>

    <div class="d-none d-md-block ms-2 lh-sm">
        <span class="d-block fw-bold" style="font-size:14px;">MediCare Hospital</span>
        <span class="d-block text-secondary" style="font-size:11.5px;">{{ now()->format('l, F j, Y') }} &middot; {{ ucfirst($me->role ?? 'User') }} portal</span>
    </div>

    <label class="mc-top-search d-none d-lg-flex ms-3" title="Filter menu">
        <i class="bi bi-search"></i>
        <input id="mc-menu-search" type="search" placeholder="{{ __('messages.staff.search_menu') }}" autocomplete="off">
    </label>

    <div class="ms-auto d-flex align-items-center gap-2">
        @if(!$isDoctor)
        <a href="{{ route('admin.appointments.index', ['status' => 0]) }}" class="top-iconbox" title="{{ __('messages.staff.pending_appointments') }}">
            <i class="bi bi-bell"></i>
            @if($navCounts['pendingAppointments'] > 0)
                <span class="top-count">{{ $navCounts['pendingAppointments'] > 9 ? '9+' : $navCounts['pendingAppointments'] }}</span>
            @endif
        </a>
        @if($isAdmin)
        <a href="{{ route('admin.comments.index') }}" class="top-iconbox" title="{{ __('messages.staff.pending_reviews') }}">
            <i class="bi bi-chat-left-text"></i>
            @if($navCounts['pendingComments'] > 0)
                <span class="top-count">{{ $navCounts['pendingComments'] > 9 ? '9+' : $navCounts['pendingComments'] }}</span>
            @endif
        </a>
        @endif
        <a href="{{ route('admin.appointments.index') }}" class="top-iconbox" title="{{ __('messages.staff.todays_appointments') }}">
            <i class="bi bi-calendar-check"></i>
            @if($navCounts['todayAppointments'] > 0)
                <span class="top-count">{{ $navCounts['todayAppointments'] > 9 ? '9+' : $navCounts['todayAppointments'] }}</span>
            @endif
        </a>
        @endif
        {{-- Kept for the notification poller in layouts/app.blade.php --}}
        <span id="mc-notif-badge" class="d-none">0</span>

        <div class="d-none d-md-block me-1 text-end top-meta">
            <p class="m-0 fw-bold text-truncate" style="max-width:150px;font-size:13px;">{{ $me->name ?? 'User' }}</p>
            <p class="m-0 small text-secondary" style="font-size:11px;">{{ ucfirst($me->role ?? 'User') }}</p>
        </div>

        <div class="dropdown">
            <button type="button" class="top-avatar dropdown-toggle-no-caret" data-bs-toggle="dropdown" aria-expanded="false" aria-label="User menu">
                <img src="{{ asset('backend-assets/images/admin.jpg') }}" alt="{{ $me->name ?? 'User' }}">
            </button>
            <ul class="dropdown-menu dropdown-menu-end mc-usermenu">
                <li class="mc-usermenu-head">
                    @php
                        $menuInitials = implode('', array_slice(array_map(fn($w) => mb_substr($w, 0, 1), explode(' ', (string) ($me->name ?? 'U'))), 0, 2));
                    @endphp
                    <span class="mc-usermenu-av">{{ strtoupper($menuInitials) }}</span>
                    <span class="min-w-0">
                        <p class="mc-usermenu-name">{{ $me->name ?? 'User' }}</p>
                        <p class="mc-usermenu-role">{{ ucfirst($me->role ?? 'User') }} · MediCare</p>
                    </span>
                </li>
                <li>
                    @if($isDoctor)
                    <a class="mc-usermenu-item" href="{{ route('doctor.profile.edit') }}">
                        <span class="mc-usermenu-ic"><i class="bi bi-person"></i></span> {{ __('messages.staff.profile') }}
                    </a>
                    @else
                    <a class="mc-usermenu-item" href="{{ route('admin.users.edit', $me->id) }}">
                        <span class="mc-usermenu-ic"><i class="bi bi-person"></i></span> {{ __('messages.staff.my_access') }}
                    </a>
                    @endif
                </li>
                <li>
                    <a class="mc-usermenu-item" href="{{ route('notifications.index') }}">
                        <span class="mc-usermenu-ic"><i class="bi bi-bell"></i></span> {{ __('messages.staff.notifications') }}
                    </a>
                </li>
                @if(!$isDoctor)
                <li>
                    <a class="mc-usermenu-item" href="{{ route('settings.general') }}">
                        <span class="mc-usermenu-ic"><i class="bi bi-gear"></i></span> {{ __('messages.staff.settings') }}
                    </a>
                </li>
                @endif
                <li><hr class="mc-usermenu-div"></li>
                <li>
                    <a class="mc-usermenu-item danger"
                       href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <span class="mc-usermenu-ic"><i class="bi bi-box-arrow-right"></i></span> {{ __('messages.staff.logout') }}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

{{-- Mobile overlay --}}
<div class="sidebar-overlay" data-toggle="sidebar"></div>

{{-- ============ Sidebar ============ --}}
<aside class="admin-sidebar">
    <a href="{{ $isDoctor ? route('doctor.dashboard') : route('admin.home') }}" class="brand">
        <span class="brand-logo">M</span>
        <span class="brand-name">MediCare<small>Hospital Admin</small></span>
    </a>

    <nav class="side-nav" aria-label="{{ $isDoctor ? 'Doctor' : 'Admin' }} navigation">
        <ul>
        @if(!$isDoctor)
            <li class="side-tree {{ request()->routeIs('admin.home', 'admin.patients.*', 'admin.doctors.*', 'admin.comments.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('admin.home', 'admin.patients.*', 'admin.doctors.*', 'admin.comments.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-speedometer2"></i>
                    <span>{{ __('messages.staff.overview') }}</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('admin.home') ? 'active' : '' }}" href="{{ route('admin.home') }}"><span class="side-dot"></span> {{ __('messages.staff.dashboard') }}</a></li>
                    @if($isAdmin || $isRecep)
                    <li><a class="side-sublink {{ request()->routeIs('admin.patients.*') ? 'active' : '' }}" href="{{ route('admin.patients.index') }}"><span class="side-dot"></span> {{ __('messages.staff.patients') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.doctors.*') ? 'active' : '' }}" href="{{ route('admin.doctors.index') }}"><span class="side-dot"></span> {{ __('messages.staff.doctors') }}</a></li>
                    @endif
                    @if($isAdmin)
                    <li>
                        <a class="side-sublink {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}" href="{{ route('admin.comments.index') }}"><span class="side-dot"></span> {{ __('messages.staff.reviews') }}
                            @if($navCounts['pendingComments'] > 0)
                                <span class="side-minibadge">{{ $navCounts['pendingComments'] }}</span>
                            @endif
                        </a>
                    </li>
                    @endif
                </ul>
            </li>

            @if($isAdmin || $isRecep)
            <li class="side-tree {{ request()->routeIs('admin.appointments.*', 'admin.time-slots.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('admin.appointments.*', 'admin.time-slots.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-calendar-check"></i>
                    <span>{{ __('messages.staff.appointments') }}</span>
                    @if($navCounts['pendingAppointments'] > 0)
                        <span class="side-badge">{{ $navCounts['pendingAppointments'] }}</span>
                    @endif
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('admin.appointments.index') ? 'active' : '' }}" href="{{ route('admin.appointments.index') }}"><span class="side-dot"></span> {{ __('messages.staff.all_appointments') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.appointments.create') ? 'active' : '' }}" href="{{ route('admin.appointments.create') }}"><span class="side-dot"></span> {{ __('messages.staff.book_appointment') }}</a></li>
                    @if($isAdmin)
                    <li><a class="side-sublink {{ request()->routeIs('admin.time-slots.*') ? 'active' : '' }}" href="{{ route('admin.time-slots.index') }}"><span class="side-dot"></span> {{ __('messages.staff.time_slots') }}</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if($isAdmin || $isRecep || $isLabTech)
            <li class="side-tree {{ request()->routeIs('admin.lab-tests.*', 'admin.lab-orders.*', 'admin.invoices.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('admin.lab-tests.*', 'admin.lab-orders.*', 'admin.invoices.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-clipboard2-pulse"></i>
                    <span>{{ __('messages.staff.laboratory') }}</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    @if(($isAdmin || $isLabTech) && \App\Support\Module::enabled('lab'))
                    <li><a class="side-sublink {{ request()->routeIs('admin.lab-tests.index') ? 'active' : '' }}" href="{{ route('admin.lab-tests.index') }}"><span class="side-dot"></span> {{ __('messages.staff.lab_tests') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.lab-tests.create') ? 'active' : '' }}" href="{{ route('admin.lab-tests.create') }}"><span class="side-dot"></span> {{ __('messages.staff.add_test') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.lab-orders.*') ? 'active' : '' }}" href="{{ route('admin.lab-orders.index') }}"><span class="side-dot"></span> {{ __('messages.staff.lab_orders') }}</a></li>
                    @endif
                    @if(($isAdmin || $isRecep) && \App\Support\Module::enabled('billing'))
                    <li><a class="side-sublink {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}" href="{{ route('admin.invoices.index') }}"><span class="side-dot"></span> {{ __('messages.staff.invoices') }}</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if(($isAdmin || $isPharm) && \App\Support\Module::enabled('prescriptions'))
            <li class="side-tree">
                <a class="side-link {{ request()->routeIs('admin.prescriptions.*') ? 'active' : '' }}" href="{{ route('admin.prescriptions.index') }}">
                    <i class="side-icon bi bi-capsule"></i>
                    <span>{{ __('messages.staff.prescriptions') }}</span>
                </a>
            </li>
            @endif

            @if($isAdmin && \App\Support\Module::enabled('vaccination'))
            <li class="side-tree">
                <a class="side-link {{ request()->routeIs('admin.vaccinations.*') ? 'active' : '' }}" href="{{ route('admin.vaccinations.index') }}">
                    <i class="side-icon bi bi-shield-plus"></i>
                    <span>{{ __('messages.staff.vaccinations') }}</span>
                </a>
            </li>
            @endif

            @if($isAdmin && \App\Support\Module::enabled('bloodbank'))
            <li class="side-tree {{ request()->routeIs('admin.bloodbank.*', 'admin.blood-groups.*', 'admin.blood-donors.*', 'admin.blood-donations.*', 'admin.blood-requests.*', 'admin.blood-issues.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('admin.bloodbank.*', 'admin.blood-groups.*', 'admin.blood-donors.*', 'admin.blood-donations.*', 'admin.blood-requests.*', 'admin.blood-issues.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-droplet"></i>
                    <span>{{ __('messages.staff.blood_bank') }}</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('admin.bloodbank.dashboard') ? 'active' : '' }}" href="{{ route('admin.bloodbank.dashboard') }}"><span class="side-dot"></span> {{ __('messages.staff.dashboard') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.bloodbank.inventory') ? 'active' : '' }}" href="{{ route('admin.bloodbank.inventory') }}"><span class="side-dot"></span> {{ __('messages.staff.inventory') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-groups.*') ? 'active' : '' }}" href="{{ route('admin.blood-groups.index') }}"><span class="side-dot"></span> {{ __('messages.staff.blood_groups') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-donors.*') ? 'active' : '' }}" href="{{ route('admin.blood-donors.index') }}"><span class="side-dot"></span> {{ __('messages.staff.donors') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-donations.*') ? 'active' : '' }}" href="{{ route('admin.blood-donations.index') }}"><span class="side-dot"></span> {{ __('messages.staff.donations') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-requests.*') ? 'active' : '' }}" href="{{ route('admin.blood-requests.index') }}"><span class="side-dot"></span> {{ __('messages.staff.requests') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-issues.*') ? 'active' : '' }}" href="{{ route('admin.blood-issues.index') }}"><span class="side-dot"></span> {{ __('messages.staff.issues') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.bloodbank.reports') ? 'active' : '' }}" href="{{ route('admin.bloodbank.reports') }}"><span class="side-dot"></span> {{ __('messages.staff.reports') }}</a></li>
                </ul>
            </li>

            @if(\App\Support\Module::enabled('ambulance'))
            <li class="side-tree">
                <a class="side-link {{ request()->routeIs('admin.ambulance-requests.*') ? 'active' : '' }}" href="{{ route('admin.ambulance-requests.index') }}">
                    <i class="side-icon bi bi-truck"></i>
                    <span>{{ __('messages.staff.ambulance') }}</span>
                </a>
            </li>
            @endif

            @if(\App\Support\Module::enabled('beds'))
            <li class="side-tree {{ request()->routeIs('admin.beds.*', 'admin.wards.*', 'admin.rooms.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('admin.beds.*', 'admin.wards.*', 'admin.rooms.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-hospital"></i>
                    <span>{{ __('messages.staff.inpatient') }}</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('admin.beds.index') ? 'active' : '' }}" href="{{ route('admin.beds.index') }}"><span class="side-dot"></span> {{ __('messages.staff.bed_dashboard') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.wards.*') ? 'active' : '' }}" href="{{ route('admin.wards.index') }}"><span class="side-dot"></span> {{ __('messages.staff.wards') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}" href="{{ route('admin.rooms.index') }}"><span class="side-dot"></span> {{ __('messages.staff.rooms') }}</a></li>
                </ul>
            </li>
            @endif
            @endif

            @if(($isAdmin || $isPharm) && \App\Support\Module::enabled('pharmacy'))
            <li class="side-tree {{ request()->routeIs('admin.medicines.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('admin.medicines.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-prescription2"></i>
                    <span>{{ __('messages.staff.pharmacy') }}</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('admin.medicines.index') ? 'active' : '' }}" href="{{ route('admin.medicines.index') }}"><span class="side-dot"></span> {{ __('messages.staff.medicine_stock') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.prescriptions.index') ? 'active' : '' }}" href="{{ route('admin.prescriptions.index') }}"><span class="side-dot"></span> {{ __('messages.staff.dispense') }}</a></li>
                </ul>
            </li>
            @endif

            @if($isRecep)
            <li class="side-tree {{ request()->routeIs('admin.bloodbank.*', 'admin.blood-groups.*', 'admin.blood-donors.*', 'admin.blood-donations.*', 'admin.blood-requests.*', 'admin.blood-issues.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('admin.bloodbank.*', 'admin.blood-groups.*', 'admin.blood-donors.*', 'admin.blood-donations.*', 'admin.blood-requests.*', 'admin.blood-issues.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-droplet"></i>
                    <span>{{ __('messages.staff.blood_bank') }}</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('admin.bloodbank.dashboard') ? 'active' : '' }}" href="{{ route('admin.bloodbank.dashboard') }}"><span class="side-dot"></span> {{ __('messages.staff.dashboard') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.bloodbank.inventory') ? 'active' : '' }}" href="{{ route('admin.bloodbank.inventory') }}"><span class="side-dot"></span> {{ __('messages.staff.inventory') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-groups.*') ? 'active' : '' }}" href="{{ route('admin.blood-groups.index') }}"><span class="side-dot"></span> {{ __('messages.staff.blood_groups') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-donors.*') ? 'active' : '' }}" href="{{ route('admin.blood-donors.index') }}"><span class="side-dot"></span> {{ __('messages.staff.donors') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-donations.*') ? 'active' : '' }}" href="{{ route('admin.blood-donations.index') }}"><span class="side-dot"></span> {{ __('messages.staff.donations') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-requests.*') ? 'active' : '' }}" href="{{ route('admin.blood-requests.index') }}"><span class="side-dot"></span> {{ __('messages.staff.requests') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-issues.*') ? 'active' : '' }}" href="{{ route('admin.blood-issues.index') }}"><span class="side-dot"></span> {{ __('messages.staff.issues') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.bloodbank.reports') ? 'active' : '' }}" href="{{ route('admin.bloodbank.reports') }}"><span class="side-dot"></span> {{ __('messages.staff.reports') }}</a></li>
                </ul>
            </li>
            @endif

            @if($isAdmin)
            <li class="side-tree {{ request()->routeIs('admin.departments.*', 'admin.services.*', 'admin.blogs.*', 'admin.categories.*', 'admin.tags.*', 'admin.sliders.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('admin.departments.*', 'admin.services.*', 'admin.blogs.*', 'admin.categories.*', 'admin.tags.*', 'admin.sliders.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-journal-text"></i>
                    <span>{{ __('messages.staff.content') }}</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}" href="{{ route('admin.departments.index') }}"><span class="side-dot"></span> {{ __('messages.staff.departments') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}"><span class="side-dot"></span> {{ __('messages.staff.services') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}" href="{{ route('admin.blogs.index') }}"><span class="side-dot"></span> {{ __('messages.staff.blogs') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><span class="side-dot"></span> {{ __('messages.staff.categories') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}" href="{{ route('admin.tags.index') }}"><span class="side-dot"></span> {{ __('messages.staff.tags') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}" href="{{ route('admin.sliders.index') }}"><span class="side-dot"></span> {{ __('messages.staff.sliders') }}</a></li>
                </ul>
            </li>

            <li class="side-tree {{ request()->routeIs('settings.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-gear"></i>
                    <span>{{ __('messages.staff.settings') }}</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('settings.general') ? 'active' : '' }}" href="{{ route('settings.general') }}"><span class="side-dot"></span> {{ __('messages.staff.general') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('settings.home') ? 'active' : '' }}" href="{{ route('settings.home') }}"><span class="side-dot"></span> {{ __('messages.staff.home') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('settings.about') ? 'active' : '' }}" href="{{ route('settings.about') }}"><span class="side-dot"></span> {{ __('messages.staff.about') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('settings.service') ? 'active' : '' }}" href="{{ route('settings.service') }}"><span class="side-dot"></span> {{ __('messages.staff.service') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('settings.doctor') ? 'active' : '' }}" href="{{ route('settings.doctor') }}"><span class="side-dot"></span> {{ __('messages.staff.doctor') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('settings.blog') ? 'active' : '' }}" href="{{ route('settings.blog') }}"><span class="side-dot"></span> {{ __('messages.staff.blog') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('settings.contact') ? 'active' : '' }}" href="{{ route('settings.contact') }}"><span class="side-dot"></span> {{ __('messages.staff.contact') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('settings.appointment') ? 'active' : '' }}" href="{{ route('settings.appointment') }}"><span class="side-dot"></span> {{ __('messages.staff.appointment') }}</a></li>
                </ul>
            </li>

            <li class="side-tree">
                <a class="side-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <i class="side-icon bi bi-people"></i>
                    <span>{{ __('messages.staff.staff_users') }}</span>
                </a>
            </li>

            <li class="side-tree">
                <a class="side-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}" href="{{ route('admin.activity-logs.index') }}">
                    <i class="side-icon bi bi-list-check"></i>
                    <span>{{ __('messages.staff.activity_logs') }}</span>
                </a>
            </li>
            @if(\App\Support\Module::enabled('backups'))
            <li class="side-tree">
                <a class="side-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}" href="{{ route('admin.backups.index') }}">
                    <i class="side-icon bi bi-database"></i>
                    <span>{{ __('messages.staff.backups') }}</span>
                </a>
            </li>
            @endif
            @if(\App\Support\Module::enabled('language'))
            <li class="side-tree">
                <a class="side-link {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}" href="{{ route('admin.languages.index') }}">
                    <i class="side-icon bi bi-translate"></i>
                    <span>{{ __('messages.admin.languages') }}</span>
                </a>
            </li>
            @endif
            @endif

        @else

            <li class="side-tree {{ request()->routeIs('doctor.dashboard', 'doctor.appointments', 'doctor.lab-orders.*', 'doctor.prescriptions.*', 'doctor.vaccinations.*', 'doctor.calendar.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('doctor.dashboard', 'doctor.appointments', 'doctor.lab-orders.*', 'doctor.prescriptions.*', 'doctor.vaccinations.*', 'doctor.calendar.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-speedometer2"></i>
                    <span>{{ __('messages.staff.dashboard') }}</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}" href="{{ route('doctor.dashboard') }}"><span class="side-dot"></span> {{ __('messages.staff.dashboard') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('doctor.appointments') ? 'active' : '' }}" href="{{ route('doctor.appointments') }}"><span class="side-dot"></span> {{ __('messages.staff.appointments') }}</a></li>
                    @if(\App\Support\Module::enabled('google_calendar'))
                    <li><a class="side-sublink {{ request()->routeIs('doctor.calendar.*') ? 'active' : '' }}" href="{{ route('doctor.calendar.index') }}"><span class="side-dot"></span> {{ __('messages.staff.google_calendar') }}</a></li>
                    @endif
                    @if(\App\Support\Module::enabled('lab'))
                    <li><a class="side-sublink {{ request()->routeIs('doctor.lab-orders.*') ? 'active' : '' }}" href="{{ route('doctor.lab-orders.index') }}"><span class="side-dot"></span> {{ __('messages.staff.lab_requests') }}</a></li>
                    @endif
                    @if(\App\Support\Module::enabled('prescriptions'))
                    <li><a class="side-sublink {{ request()->routeIs('doctor.prescriptions.*') ? 'active' : '' }}" href="{{ route('doctor.prescriptions.index') }}"><span class="side-dot"></span> {{ __('messages.staff.prescriptions') }}</a></li>
                    @endif
                    @if(\App\Support\Module::enabled('vaccination'))
                    <li><a class="side-sublink {{ request()->routeIs('doctor.vaccinations.*') ? 'active' : '' }}" href="{{ route('doctor.vaccinations.index') }}"><span class="side-dot"></span> {{ __('messages.staff.vaccinations') }}</a></li>
                    @endif
                </ul>
            </li>

            @if(\App\Support\Module::enabled('bloodbank'))
            <li class="side-tree {{ request()->routeIs('doctor.blood-requests.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('doctor.blood-requests.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-droplet"></i>
                    <span>{{ __('messages.staff.blood_requests') }}</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('doctor.blood-requests.index') ? 'active' : '' }}" href="{{ route('doctor.blood-requests.index') }}"><span class="side-dot"></span> {{ __('messages.staff.all_requests') }}</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('doctor.blood-requests.create') ? 'active' : '' }}" href="{{ route('doctor.blood-requests.create') }}"><span class="side-dot"></span> {{ __('messages.staff.new_request') }}</a></li>
                </ul>
            </li>
            @endif

            <li class="side-tree">
                <a class="side-link {{ request()->routeIs('doctor.profile.*') ? 'active' : '' }}" href="{{ route('doctor.profile.edit') }}">
                    <i class="side-icon bi bi-person"></i>
                    <span>{{ __('messages.staff.profile') }}</span>
                </a>
            </li>

        @endif
        </ul>
    </nav>

    <div class="side-foot">
        <p class="side-foot-title">MediCare Hospital</p>
        <p>© {{ now()->year }} Care team · v2.0 modern</p>
        <p class="mt-1">Secure clinical workspace</p>
    </div>
</aside>

{{-- Sidebar live filter (no dependency on main.js) --}}
<script>
(function () {
    var input = document.getElementById('mc-menu-search');
    if (!input) return;
    input.addEventListener('input', function () {
        var q = input.value.trim().toLowerCase();
        document.querySelectorAll('.admin-sidebar .side-tree').forEach(function (tree) {
            if (!q) { tree.style.display = ''; return; }
            var hit = (tree.textContent || '').toLowerCase().indexOf(q) !== -1;
            tree.style.display = hit ? '' : 'none';
            if (hit) tree.classList.add('is-expanded');
        });
    });
})();
</script>
