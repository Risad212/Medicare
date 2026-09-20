{{-- -----------------------------------------------------------------
     MediCare backend shell: white sidebar + white topbar + content.
     Hooks kept for main.js:
       - data-toggle="sidebar"  → toggles body.sidenav-toggled
       - data-toggle="treeview" → toggles .is-expanded on parent <li>
     The avatar menu uses its own tiny toggle script below (no
     Bootstrap dependency) — click to open, outside-click / Esc closes.
  ----------------------------------------------------------------- --}}

@php
    $me = auth()->user();
    $isDoctor = $me && $me->role === 'doctor';
    $navCounts = Cache::remember('mc.nav.counts', 60, fn () => [
        'pendingAppointments' => App\Models\Appointment::where('status', 0)->count(),
        'pendingComments' => App\Models\BlogComment::where('status', 0)->count(),
        'todayAppointments' => App\Models\Appointment::whereDate('appointment_date', now()->toDateString())->count(),
    ]);
@endphp

{{-- ============ Topbar ============ --}}
<header class="mc-header">
    <button type="button" data-toggle="sidebar" class="welly-iconbtn lg:hidden" aria-label="Toggle Sidebar">
        <i class="bi bi-list"></i>
    </button>
    <button type="button" data-toggle="sidebar" class="welly-iconbtn -ml-2 hidden lg:inline-flex" aria-label="Collapse Sidebar">
        <i class="bi bi-list"></i>
    </button>

    <div class="ml-2 hidden flex-col leading-tight md:flex">
        <span class="text-[14px] font-extrabold tracking-tight text-ink">MediCare Hospital</span>
        <span class="text-[12px] text-mut">{{ now()->format('l, F j, Y') }}</span>
    </div>

    <div class="ml-auto flex items-center gap-2.5">
        @if(!$isDoctor)
        <a href="{{ route('admin.appointments.index', ['status' => 0]) }}" class="welly-iconbox" title="Pending appointments">
            <i class="bi bi-bell"></i>
            @if($navCounts['pendingAppointments'] > 0)
                <span class="welly-count">{{ $navCounts['pendingAppointments'] > 9 ? '9+' : $navCounts['pendingAppointments'] }}</span>
            @endif
        </a>
        <a href="{{ route('admin.comments.index') }}" class="welly-iconbox" title="Pending reviews">
            <i class="bi bi-chat-left-text"></i>
            @if($navCounts['pendingComments'] > 0)
                <span class="welly-count">{{ $navCounts['pendingComments'] > 9 ? '9+' : $navCounts['pendingComments'] }}</span>
            @endif
        </a>
        <a href="{{ route('admin.appointments.index') }}" class="welly-iconbox" title="Today's appointments">
            <i class="bi bi-calendar-check"></i>
            @if($navCounts['todayAppointments'] > 0)
                <span class="welly-count">{{ $navCounts['todayAppointments'] > 9 ? '9+' : $navCounts['todayAppointments'] }}</span>
            @endif
        </a>
        @endif
        {{-- Kept for the notification poller in layouts/app.blade.php --}}
        <span id="mc-notif-badge" class="hidden" style="display:none">0</span>

        <div class="mr-1 hidden text-right leading-tight md:block">
            <p class="m-0 max-w-[150px] truncate text-[13px] font-bold text-ink">{{ $me->name ?? 'User' }}</p>
            <p class="m-0 text-[11px] text-mut">{{ ucfirst($me->role ?? 'User') }}</p>
        </div>

        <div class="relative" id="welly-user">
            <button type="button" id="welly-user-btn" aria-haspopup="true" aria-expanded="false" aria-label="User menu" class="welly-avatar-btn">
                <img src="{{ asset('backend-assets/images/admin.jpg') }}" alt="{{ $me->name ?? 'User' }}">
            </button>
            <ul id="welly-user-menu" class="absolute right-0 z-40 mt-2 hidden w-52 overflow-hidden rounded-xl border border-line bg-white py-1.5 text-sm text-ink shadow-card">
                <li class="px-4 pb-1.5 pt-2">
                    <p class="m-0 truncate text-[14px] font-bold text-ink">{{ $me->name ?? 'User' }}</p>
                    <p class="m-0 truncate text-[12px] text-mut">{{ ucfirst($me->role ?? 'User') }} · MediCare</p>
                </li>
                <li><hr class="my-1.5 border-line"></li>
                <li>
                    <a class="flex items-center gap-2.5 px-4 py-2 text-ink-2 transition-colors hover:bg-line-2" href="{{ $isDoctor ? route('doctor.profile.edit') : route('profile') }}">
                        <i class="bi bi-person text-base text-mut"></i> Profile
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-2.5 px-4 py-2 text-ink-2 transition-colors hover:bg-line-2" href="{{ route('notifications.index') }}">
                        <i class="bi bi-bell text-base text-mut"></i> Notifications
                    </a>
                </li>
                @if(!$isDoctor)
                <li>
                    <a class="flex items-center gap-2.5 px-4 py-2 text-ink-2 transition-colors hover:bg-line-2" href="{{ route('settings.general') }}">
                        <i class="bi bi-gear text-base text-mut"></i> Settings
                    </a>
                </li>
                @endif
                <li><hr class="my-1.5 border-line"></li>
                <li>
                    <a class="flex items-center gap-2.5 px-4 py-2 text-ink-2 transition-colors hover:bg-line-2"
                       href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right text-base text-mut"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

<script>
    (function () {
        var wrap = document.getElementById('welly-user');
        var btn = document.getElementById('welly-user-btn');
        var menu = document.getElementById('welly-user-menu');
        if (!wrap || !btn || !menu) return;
        function close() { menu.classList.add('hidden'); btn.setAttribute('aria-expanded', 'false'); }
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
            } else {
                close();
            }
        });
        document.addEventListener('click', function (e) {
            if (!menu.classList.contains('hidden') && !wrap.contains(e.target)) close();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') close();
        });
    })();
</script>

{{-- Mobile overlay --}}
<div class="mc-sidebar-overlay" data-toggle="sidebar"></div>

{{-- ============ Sidebar ============ --}}
<aside class="mc-sidebar">
    <a href="{{ $isDoctor ? route('doctor.dashboard') : route('admin.home') }}" class="welly-brand">
        <span class="welly-logo">M</span>
        <span class="welly-brand-name">MediCare</span>
    </a>

    <nav class="mc-nav" aria-label="{{ $isDoctor ? 'Doctor' : 'Admin' }} navigation">

        @if(!$isDoctor)
            <li class="mc-treeview {{ request()->routeIs('admin.home', 'admin.patients.*', 'admin.doctors.*', 'admin.comments.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('admin.home', 'admin.patients.*', 'admin.doctors.*', 'admin.comments.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.home') ? 'active' : '' }}" href="{{ route('admin.home') }}"><span class="mc-dot"></span> Dashboard</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.patients.*') ? 'active' : '' }}" href="{{ route('admin.patients.index') }}"><span class="mc-dot"></span> Patients</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.doctors.*') ? 'active' : '' }}" href="{{ route('admin.doctors.index') }}"><span class="mc-dot"></span> Doctors</a></li>
                    <li>
                        <a class="mc-treeview-item {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}" href="{{ route('admin.comments.index') }}"><span class="mc-dot"></span> Reviews
                            @if($navCounts['pendingComments'] > 0)
                                <span class="welly-mini-badge">{{ $navCounts['pendingComments'] }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </li>

            <li class="mc-treeview {{ request()->routeIs('admin.appointments.*', 'admin.time-slots.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('admin.appointments.*', 'admin.time-slots.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-calendar-check"></i>
                    <span>Appointments</span>
                    @if($navCounts['pendingAppointments'] > 0)
                        <span class="mc-nav__badge">{{ $navCounts['pendingAppointments'] }}</span>
                    @endif
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.appointments.index') ? 'active' : '' }}" href="{{ route('admin.appointments.index') }}"><span class="mc-dot"></span> All Appointments</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.appointments.create') ? 'active' : '' }}" href="{{ route('admin.appointments.create') }}"><span class="mc-dot"></span> Book Appointment</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.time-slots.*') ? 'active' : '' }}" href="{{ route('admin.time-slots.index') }}"><span class="mc-dot"></span> Time Slots</a></li>
                </ul>
            </li>

            <li class="mc-treeview {{ request()->routeIs('admin.lab-tests.*', 'admin.lab-orders.*', 'admin.invoices.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('admin.lab-tests.*', 'admin.lab-orders.*', 'admin.invoices.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-clipboard2-pulse"></i>
                    <span>Laboratory</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.lab-tests.index') ? 'active' : '' }}" href="{{ route('admin.lab-tests.index') }}"><span class="mc-dot"></span> Lab Tests</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.lab-tests.create') ? 'active' : '' }}" href="{{ route('admin.lab-tests.create') }}"><span class="mc-dot"></span> Add Test</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.lab-orders.*') ? 'active' : '' }}" href="{{ route('admin.lab-orders.index') }}"><span class="mc-dot"></span> Lab Orders</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}" href="{{ route('admin.invoices.index') }}"><span class="mc-dot"></span> Invoices</a></li>
                </ul>
            </li>

            <a class="mc-nav__item {{ request()->routeIs('admin.prescriptions.*') ? 'active' : '' }}" href="{{ route('admin.prescriptions.index') }}">
                <i class="mc-nav-icon bi bi-capsule"></i>
                <span>Prescriptions</span>
            </a>

            <li class="mc-treeview {{ request()->routeIs('admin.bloodbank.*', 'admin.blood-groups.*', 'admin.blood-donors.*', 'admin.blood-donations.*', 'admin.blood-requests.*', 'admin.blood-issues.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('admin.bloodbank.*', 'admin.blood-groups.*', 'admin.blood-donors.*', 'admin.blood-donations.*', 'admin.blood-requests.*', 'admin.blood-issues.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-droplet"></i>
                    <span>Blood Bank</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.bloodbank.dashboard') ? 'active' : '' }}" href="{{ route('admin.bloodbank.dashboard') }}"><span class="mc-dot"></span> Dashboard</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.bloodbank.inventory') ? 'active' : '' }}" href="{{ route('admin.bloodbank.inventory') }}"><span class="mc-dot"></span> Inventory</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.blood-groups.*') ? 'active' : '' }}" href="{{ route('admin.blood-groups.index') }}"><span class="mc-dot"></span> Blood Groups</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.blood-donors.*') ? 'active' : '' }}" href="{{ route('admin.blood-donors.index') }}"><span class="mc-dot"></span> Donors</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.blood-donations.*') ? 'active' : '' }}" href="{{ route('admin.blood-donations.index') }}"><span class="mc-dot"></span> Donations</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.blood-requests.*') ? 'active' : '' }}" href="{{ route('admin.blood-requests.index') }}"><span class="mc-dot"></span> Requests</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.blood-issues.*') ? 'active' : '' }}" href="{{ route('admin.blood-issues.index') }}"><span class="mc-dot"></span> Issues</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.bloodbank.reports') ? 'active' : '' }}" href="{{ route('admin.bloodbank.reports') }}"><span class="mc-dot"></span> Reports</a></li>
                </ul>
            </li>

            <li class="mc-treeview {{ request()->routeIs('admin.departments.*', 'admin.services.*', 'admin.blogs.*', 'admin.categories.*', 'admin.tags.*', 'admin.sliders.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('admin.departments.*', 'admin.services.*', 'admin.blogs.*', 'admin.categories.*', 'admin.tags.*', 'admin.sliders.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-journal-text"></i>
                    <span>Content</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}" href="{{ route('admin.departments.index') }}"><span class="mc-dot"></span> Departments</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}"><span class="mc-dot"></span> Services</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}" href="{{ route('admin.blogs.index') }}"><span class="mc-dot"></span> Blogs</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><span class="mc-dot"></span> Categories</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}" href="{{ route('admin.tags.index') }}"><span class="mc-dot"></span> Tags</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}" href="{{ route('admin.sliders.index') }}"><span class="mc-dot"></span> Sliders</a></li>
                </ul>
            </li>

            <li class="mc-treeview {{ request()->routeIs('settings.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-gear"></i>
                    <span>Settings</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul>
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.general') ? 'active' : '' }}" href="{{ route('settings.general') }}"><span class="mc-dot"></span> General</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.home') ? 'active' : '' }}" href="{{ route('settings.home') }}"><span class="mc-dot"></span> Home</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.about') ? 'active' : '' }}" href="{{ route('settings.about') }}"><span class="mc-dot"></span> About</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.service') ? 'active' : '' }}" href="{{ route('settings.service') }}"><span class="mc-dot"></span> Service</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.doctor') ? 'active' : '' }}" href="{{ route('settings.doctor') }}"><span class="mc-dot"></span> Doctor</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.blog') ? 'active' : '' }}" href="{{ route('settings.blog') }}"><span class="mc-dot"></span> Blog</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.contact') ? 'active' : '' }}" href="{{ route('settings.contact') }}"><span class="mc-dot"></span> Contact</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.appointment') ? 'active' : '' }}" href="{{ route('settings.appointment') }}"><span class="mc-dot"></span> Appointment</a></li>
                </ul>
            </li>

            <a class="mc-nav__item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                <i class="mc-nav-icon bi bi-people"></i>
                <span>Staff &amp; Users</span>
            </a>

            <a class="mc-nav__item {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}" href="{{ route('admin.activity-logs.index') }}">
                <i class="mc-nav-icon bi bi-list-check"></i>
                <span>Activity Logs</span>
            </a>

        @else

            <li class="mc-treeview {{ request()->routeIs('doctor.dashboard', 'doctor.appointments', 'doctor.lab-orders.*', 'doctor.prescriptions.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('doctor.dashboard', 'doctor.appointments', 'doctor.lab-orders.*', 'doctor.prescriptions.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul>
                    <li><a class="mc-treeview-item {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}" href="{{ route('doctor.dashboard') }}"><span class="mc-dot"></span> Dashboard</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('doctor.appointments') ? 'active' : '' }}" href="{{ route('doctor.appointments') }}"><span class="mc-dot"></span> Appointments</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('doctor.lab-orders.*') ? 'active' : '' }}" href="{{ route('doctor.lab-orders.index') }}"><span class="mc-dot"></span> Lab Requests</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('doctor.prescriptions.*') ? 'active' : '' }}" href="{{ route('doctor.prescriptions.index') }}"><span class="mc-dot"></span> Prescriptions</a></li>
                </ul>
            </li>

            <li class="mc-treeview {{ request()->routeIs('doctor.blood-requests.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('doctor.blood-requests.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-droplet"></i>
                    <span>Blood Requests</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul>
                    <li><a class="mc-treeview-item {{ request()->routeIs('doctor.blood-requests.index') ? 'active' : '' }}" href="{{ route('doctor.blood-requests.index') }}"><span class="mc-dot"></span> All Requests</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('doctor.blood-requests.create') ? 'active' : '' }}" href="{{ route('doctor.blood-requests.create') }}"><span class="mc-dot"></span> New Request</a></li>
                </ul>
            </li>

            <a class="mc-nav__item {{ request()->routeIs('doctor.profile.*') ? 'active' : '' }}" href="{{ route('doctor.profile.edit') }}">
                <i class="mc-nav-icon bi bi-person"></i>
                <span>Profile</span>
            </a>

        @endif

    </nav>

    <div class="welly-side-foot">
        <p class="welly-side-foot-title">MediCare Hospital Admin</p>
        <p>© {{ now()->year }} All Rights Reserved</p>
        <p class="mt-1">Made with ♥ for care teams</p>
    </div>
</aside>
