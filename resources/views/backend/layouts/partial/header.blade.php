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
        <span class="d-block fs-6 fw-bold">MediCare Hospital</span>
        <span class="d-block small text-secondary">{{ now()->format('l, F j, Y') }}</span>
    </div>

    <div class="ms-auto d-flex align-items-center gap-2">
        @if(!$isDoctor)
        <a href="{{ route('admin.appointments.index', ['status' => 0]) }}" class="top-iconbox" title="Pending appointments">
            <i class="bi bi-bell"></i>
            @if($navCounts['pendingAppointments'] > 0)
                <span class="top-count">{{ $navCounts['pendingAppointments'] > 9 ? '9+' : $navCounts['pendingAppointments'] }}</span>
            @endif
        </a>
        @if($isAdmin)
        <a href="{{ route('admin.comments.index') }}" class="top-iconbox" title="Pending reviews">
            <i class="bi bi-chat-left-text"></i>
            @if($navCounts['pendingComments'] > 0)
                <span class="top-count">{{ $navCounts['pendingComments'] > 9 ? '9+' : $navCounts['pendingComments'] }}</span>
            @endif
        </a>
        @endif
        <a href="{{ route('admin.appointments.index') }}" class="top-iconbox" title="Today's appointments">
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
                        <span class="mc-usermenu-ic"><i class="bi bi-person"></i></span> Profile
                    </a>
                    @else
                    <a class="mc-usermenu-item" href="{{ route('admin.users.edit', $me->id) }}">
                        <span class="mc-usermenu-ic"><i class="bi bi-person"></i></span> My Access
                    </a>
                    @endif
                </li>
                <li>
                    <a class="mc-usermenu-item" href="{{ route('notifications.index') }}">
                        <span class="mc-usermenu-ic"><i class="bi bi-bell"></i></span> Notifications
                    </a>
                </li>
                @if(!$isDoctor)
                <li>
                    <a class="mc-usermenu-item" href="{{ route('settings.general') }}">
                        <span class="mc-usermenu-ic"><i class="bi bi-gear"></i></span> Settings
                    </a>
                </li>
                @endif
                <li><hr class="mc-usermenu-div"></li>
                <li>
                    <a class="mc-usermenu-item danger"
                       href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <span class="mc-usermenu-ic"><i class="bi bi-box-arrow-right"></i></span> Logout
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
        <span class="brand-name">MediCare</span>
    </a>

    <nav class="side-nav" aria-label="{{ $isDoctor ? 'Doctor' : 'Admin' }} navigation">
        <ul>
        @if(!$isDoctor)
            <li class="side-tree {{ request()->routeIs('admin.home', 'admin.patients.*', 'admin.doctors.*', 'admin.comments.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('admin.home', 'admin.patients.*', 'admin.doctors.*', 'admin.comments.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('admin.home') ? 'active' : '' }}" href="{{ route('admin.home') }}"><span class="side-dot"></span> Dashboard</a></li>
                    @if($isAdmin || $isRecep)
                    <li><a class="side-sublink {{ request()->routeIs('admin.patients.*') ? 'active' : '' }}" href="{{ route('admin.patients.index') }}"><span class="side-dot"></span> Patients</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.doctors.*') ? 'active' : '' }}" href="{{ route('admin.doctors.index') }}"><span class="side-dot"></span> Doctors</a></li>
                    @endif
                    @if($isAdmin)
                    <li>
                        <a class="side-sublink {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}" href="{{ route('admin.comments.index') }}"><span class="side-dot"></span> Reviews
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
                    <span>Appointments</span>
                    @if($navCounts['pendingAppointments'] > 0)
                        <span class="side-badge">{{ $navCounts['pendingAppointments'] }}</span>
                    @endif
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('admin.appointments.index') ? 'active' : '' }}" href="{{ route('admin.appointments.index') }}"><span class="side-dot"></span> All Appointments</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.appointments.create') ? 'active' : '' }}" href="{{ route('admin.appointments.create') }}"><span class="side-dot"></span> Book Appointment</a></li>
                    @if($isAdmin)
                    <li><a class="side-sublink {{ request()->routeIs('admin.time-slots.*') ? 'active' : '' }}" href="{{ route('admin.time-slots.index') }}"><span class="side-dot"></span> Time Slots</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if($isAdmin || $isRecep || $isLabTech)
            <li class="side-tree {{ request()->routeIs('admin.lab-tests.*', 'admin.lab-orders.*', 'admin.invoices.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('admin.lab-tests.*', 'admin.lab-orders.*', 'admin.invoices.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-clipboard2-pulse"></i>
                    <span>Laboratory</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    @if($isAdmin || $isLabTech)
                    <li><a class="side-sublink {{ request()->routeIs('admin.lab-tests.index') ? 'active' : '' }}" href="{{ route('admin.lab-tests.index') }}"><span class="side-dot"></span> Lab Tests</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.lab-tests.create') ? 'active' : '' }}" href="{{ route('admin.lab-tests.create') }}"><span class="side-dot"></span> Add Test</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.lab-orders.*') ? 'active' : '' }}" href="{{ route('admin.lab-orders.index') }}"><span class="side-dot"></span> Lab Orders</a></li>
                    @endif
                    @if($isAdmin || $isRecep)
                    <li><a class="side-sublink {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}" href="{{ route('admin.invoices.index') }}"><span class="side-dot"></span> Invoices</a></li>
                    @endif
                </ul>
            </li>
            @endif

            @if($isAdmin || $isPharm)
            <li class="side-tree">
                <a class="side-link {{ request()->routeIs('admin.prescriptions.*') ? 'active' : '' }}" href="{{ route('admin.prescriptions.index') }}">
                    <i class="side-icon bi bi-capsule"></i>
                    <span>Prescriptions</span>
                </a>
            </li>
            @endif

            @if($isAdmin)
            <li class="side-tree {{ request()->routeIs('admin.bloodbank.*', 'admin.blood-groups.*', 'admin.blood-donors.*', 'admin.blood-donations.*', 'admin.blood-requests.*', 'admin.blood-issues.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('admin.bloodbank.*', 'admin.blood-groups.*', 'admin.blood-donors.*', 'admin.blood-donations.*', 'admin.blood-requests.*', 'admin.blood-issues.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-droplet"></i>
                    <span>Blood Bank</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('admin.bloodbank.dashboard') ? 'active' : '' }}" href="{{ route('admin.bloodbank.dashboard') }}"><span class="side-dot"></span> Dashboard</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.bloodbank.inventory') ? 'active' : '' }}" href="{{ route('admin.bloodbank.inventory') }}"><span class="side-dot"></span> Inventory</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-groups.*') ? 'active' : '' }}" href="{{ route('admin.blood-groups.index') }}"><span class="side-dot"></span> Blood Groups</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-donors.*') ? 'active' : '' }}" href="{{ route('admin.blood-donors.index') }}"><span class="side-dot"></span> Donors</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-donations.*') ? 'active' : '' }}" href="{{ route('admin.blood-donations.index') }}"><span class="side-dot"></span> Donations</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-requests.*') ? 'active' : '' }}" href="{{ route('admin.blood-requests.index') }}"><span class="side-dot"></span> Requests</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blood-issues.*') ? 'active' : '' }}" href="{{ route('admin.blood-issues.index') }}"><span class="side-dot"></span> Issues</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.bloodbank.reports') ? 'active' : '' }}" href="{{ route('admin.bloodbank.reports') }}"><span class="side-dot"></span> Reports</a></li>
                </ul>
            </li>

            <li class="side-tree {{ request()->routeIs('admin.departments.*', 'admin.services.*', 'admin.blogs.*', 'admin.categories.*', 'admin.tags.*', 'admin.sliders.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('admin.departments.*', 'admin.services.*', 'admin.blogs.*', 'admin.categories.*', 'admin.tags.*', 'admin.sliders.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-journal-text"></i>
                    <span>Content</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}" href="{{ route('admin.departments.index') }}"><span class="side-dot"></span> Departments</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}"><span class="side-dot"></span> Services</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}" href="{{ route('admin.blogs.index') }}"><span class="side-dot"></span> Blogs</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><span class="side-dot"></span> Categories</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}" href="{{ route('admin.tags.index') }}"><span class="side-dot"></span> Tags</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}" href="{{ route('admin.sliders.index') }}"><span class="side-dot"></span> Sliders</a></li>
                </ul>
            </li>

            <li class="side-tree {{ request()->routeIs('settings.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-gear"></i>
                    <span>Settings</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('settings.general') ? 'active' : '' }}" href="{{ route('settings.general') }}"><span class="side-dot"></span> General</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('settings.home') ? 'active' : '' }}" href="{{ route('settings.home') }}"><span class="side-dot"></span> Home</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('settings.about') ? 'active' : '' }}" href="{{ route('settings.about') }}"><span class="side-dot"></span> About</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('settings.service') ? 'active' : '' }}" href="{{ route('settings.service') }}"><span class="side-dot"></span> Service</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('settings.doctor') ? 'active' : '' }}" href="{{ route('settings.doctor') }}"><span class="side-dot"></span> Doctor</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('settings.blog') ? 'active' : '' }}" href="{{ route('settings.blog') }}"><span class="side-dot"></span> Blog</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('settings.contact') ? 'active' : '' }}" href="{{ route('settings.contact') }}"><span class="side-dot"></span> Contact</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('settings.appointment') ? 'active' : '' }}" href="{{ route('settings.appointment') }}"><span class="side-dot"></span> Appointment</a></li>
                </ul>
            </li>

            <li class="side-tree">
                <a class="side-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <i class="side-icon bi bi-people"></i>
                    <span>Staff &amp; Users</span>
                </a>
            </li>

            <li class="side-tree">
                <a class="side-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}" href="{{ route('admin.activity-logs.index') }}">
                    <i class="side-icon bi bi-list-check"></i>
                    <span>Activity Logs</span>
                </a>
            </li>
            @endif

        @else

            <li class="side-tree {{ request()->routeIs('doctor.dashboard', 'doctor.appointments', 'doctor.lab-orders.*', 'doctor.prescriptions.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('doctor.dashboard', 'doctor.appointments', 'doctor.lab-orders.*', 'doctor.prescriptions.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}" href="{{ route('doctor.dashboard') }}"><span class="side-dot"></span> Dashboard</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('doctor.appointments') ? 'active' : '' }}" href="{{ route('doctor.appointments') }}"><span class="side-dot"></span> Appointments</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('doctor.lab-orders.*') ? 'active' : '' }}" href="{{ route('doctor.lab-orders.index') }}"><span class="side-dot"></span> Lab Requests</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('doctor.prescriptions.*') ? 'active' : '' }}" href="{{ route('doctor.prescriptions.index') }}"><span class="side-dot"></span> Prescriptions</a></li>
                </ul>
            </li>

            <li class="side-tree {{ request()->routeIs('doctor.blood-requests.*') ? 'is-expanded' : '' }}">
                <a class="side-link {{ request()->routeIs('doctor.blood-requests.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="side-icon bi bi-droplet"></i>
                    <span>Blood Requests</span>
                    <i class="side-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="side-sub">
                    <li><a class="side-sublink {{ request()->routeIs('doctor.blood-requests.index') ? 'active' : '' }}" href="{{ route('doctor.blood-requests.index') }}"><span class="side-dot"></span> All Requests</a></li>
                    <li><a class="side-sublink {{ request()->routeIs('doctor.blood-requests.create') ? 'active' : '' }}" href="{{ route('doctor.blood-requests.create') }}"><span class="side-dot"></span> New Request</a></li>
                </ul>
            </li>

            <li class="side-tree">
                <a class="side-link {{ request()->routeIs('doctor.profile.*') ? 'active' : '' }}" href="{{ route('doctor.profile.edit') }}">
                    <i class="side-icon bi bi-person"></i>
                    <span>Profile</span>
                </a>
            </li>

        @endif
        </ul>
    </nav>

    <div class="side-foot">
        <p class="side-foot-title">MediCare Hospital Admin</p>
        <p>© {{ now()->year }} All Rights Reserved</p>
        <p class="mt-1">Made with ♥ for care teams</p>
    </div>
</aside>
