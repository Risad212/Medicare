{{-- -----------------------------------------------------------------
     Backend shell header + sidebar
     Hooks kept for main.js:
       - data-toggle="sidebar"  → toggles body.sidenav-toggled
       - data-toggle="treeview" → toggles .is-expanded on parent <li>
  ----------------------------------------------------------------- }}

<header class="mc-header">
    {{-- Sidebar toggle (hamburger) --}}
    <a href="#" data-toggle="sidebar" class="flex h-full items-center text-white/90 hover:text-white lg:hidden" aria-label="Toggle Sidebar">
        <i class="bi bi-list text-[22px]"></i>
    </a>

    {{-- Logo (desktop only, inline with sidebar width) --}}
    <a href="{{ auth()->user()->role === 'doctor' ? route('doctor.dashboard') : route('admin.home') }}"
       class="hidden h-[56px] items-center px-6 text-lg font-bold tracking-tight text-white no-underline lg:flex lg:w-[240px] lg:bg-teal-dk">
        MediCare
    </a>

    {{-- Spacer --}}
    <div class="flex-1"></div>

    {{-- User dropdown --}}
    <div class="relative">
        <button type="button"
                class="flex h-9 w-9 items-center justify-center rounded-full bg-white/15 text-sm font-bold text-white transition-colors hover:bg-white/25"
                data-bs-toggle="dropdown" aria-expanded="false" aria-label="User menu">
            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end absolute right-0 mt-2 w-48 rounded-xl border border-line bg-white py-1.5 text-sm text-ink shadow-card">
            @if(auth()->user()->role === 'doctor')
                <li>
                    <a class="flex items-center gap-2.5 px-4 py-2 text-ink-2 transition-colors hover:bg-line-2" href="{{ route('doctor.profile.edit') }}">
                        <i class="bi bi-person text-base text-mut"></i> Profile
                    </a>
                </li>
                <li><hr class="my-1.5 border-line"></li>
            @endif
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
</header>

{{-- Mobile overlay --}}
<div class="mc-sidebar-overlay" data-toggle="sidebar"></div>

{{-- Sidebar --}}
<aside class="mc-sidebar">
    {{-- User --}}
    <div class="mc-sidebar-user">
        <img class="mc-sidebar-user-avatar" src="{{ asset('backend-assets/images/admin.jpg') }}" alt="{{ auth()->user()->name }}">
        <div class="min-w-0">
            <p class="mc-sidebar-user-name">{{ auth()->user()->name }}</p>
            <p class="mc-sidebar-user-role">{{ ucfirst(auth()->user()->role ?? 'User') }}</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="mc-nav" aria-label="Admin navigation">

        @if(auth()->user()->role === 'admin')
            @php
                $navCounts = Cache::remember('mc.nav.counts', 60, fn () => [
                    'pendingAppointments' => App\Models\Appointment::where('status', 0)->count(),
                    'pendingComments' => App\Models\BlogComment::where('status', 0)->count(),
                ]);
            @endphp

            <span class="mc-nav__group">Clinic</span>

            <a class="mc-nav__item {{ request()->routeIs('admin.home') ? 'active' : '' }}" href="{{ route('admin.home') }}">
                <i class="mc-nav-icon bi bi-speedometer"></i>
                <span>Dashboard</span>
            </a>

            <li class="mc-treeview {{ request()->routeIs('admin.appointments.*', 'admin.time-slots.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('admin.appointments.*', 'admin.time-slots.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-calendar-check"></i>
                    <span>Appointments</span>
                    @if($navCounts['pendingAppointments'] > 0)
                        <span class="mc-nav__badge">{{ $navCounts['pendingAppointments'] }}</span>
                    @endif
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="flex flex-col gap-0.5 py-1 pl-4">
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}" href="{{ route('admin.appointments.index') }}"><span class="mc-dot"></span> All Appointments</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.time-slots.*') ? 'active' : '' }}" href="{{ route('admin.time-slots.index') }}"><span class="mc-dot"></span> Time Slots</a></li>
                </ul>
            </li>

            <li class="mc-treeview {{ request()->routeIs('admin.doctors.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('admin.doctors.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-person-badge"></i>
                    <span>Doctors</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="flex flex-col gap-0.5 py-1 pl-4">
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.doctors.index') ? 'active' : '' }}" href="{{ route('admin.doctors.index') }}"><span class="mc-dot"></span> All Doctors</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.doctors.create') ? 'active' : '' }}" href="{{ route('admin.doctors.create') }}"><span class="mc-dot"></span> Add Doctor</a></li>
                </ul>
            </li>

            <a class="mc-nav__item {{ request()->routeIs('admin.patients.*') ? 'active' : '' }}" href="{{ route('admin.patients.index') }}">
                <i class="mc-nav-icon bi bi-person-heart"></i>
                <span>Patients</span>
            </a>

            <li class="mc-treeview {{ request()->routeIs('admin.comments.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-chat-left-text"></i>
                    <span>Reviews</span>
                    @if($navCounts['pendingComments'] > 0)
                        <span class="mc-nav__badge">{{ $navCounts['pendingComments'] }}</span>
                    @endif
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="flex flex-col gap-0.5 py-1 pl-4">
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}" href="{{ route('admin.comments.index') }}"><span class="mc-dot"></span> Comments</a></li>
                </ul>
            </li>

            <span class="mc-nav__group">Manage</span>

            <li class="mc-treeview {{ request()->routeIs('admin.departments.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-building"></i>
                    <span>Departments</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="flex flex-col gap-0.5 py-1 pl-4">
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.departments.index') ? 'active' : '' }}" href="{{ route('admin.departments.index') }}"><span class="mc-dot"></span> Department List</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.departments.create') ? 'active' : '' }}" href="{{ route('admin.departments.create') }}"><span class="mc-dot"></span> Add Department</a></li>
                </ul>
            </li>

            <li class="mc-treeview {{ request()->routeIs('admin.blogs.*', 'admin.categories.*', 'admin.tags.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('admin.blogs.*', 'admin.categories.*', 'admin.tags.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-journal-text"></i>
                    <span>Blogs</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="flex flex-col gap-0.5 py-1 pl-4">
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.blogs.index') ? 'active' : '' }}" href="{{ route('admin.blogs.index') }}"><span class="mc-dot"></span> All Blogs</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.blogs.create') ? 'active' : '' }}" href="{{ route('admin.blogs.create') }}"><span class="mc-dot"></span> Add New</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><span class="mc-dot"></span> Categories</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.tags.*') ? 'active' : '' }}" href="{{ route('admin.tags.index') }}"><span class="mc-dot"></span> Tags</a></li>
                </ul>
            </li>

            <li class="mc-treeview {{ request()->routeIs('admin.sliders.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-images"></i>
                    <span>Sliders</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="flex flex-col gap-0.5 py-1 pl-4">
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.sliders.index') ? 'active' : '' }}" href="{{ route('admin.sliders.index') }}"><span class="mc-dot"></span> All Sliders</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.sliders.create') ? 'active' : '' }}" href="{{ route('admin.sliders.create') }}"><span class="mc-dot"></span> Add New</a></li>
                </ul>
            </li>

            <li class="mc-treeview {{ request()->routeIs('admin.lab-tests.*', 'admin.lab-orders.*', 'admin.invoices.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('admin.lab-tests.*', 'admin.lab-orders.*', 'admin.invoices.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-clipboard2-pulse"></i>
                    <span>Laboratory</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="flex flex-col gap-0.5 py-1 pl-4">
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.lab-tests.index') ? 'active' : '' }}" href="{{ route('admin.lab-tests.index') }}"><span class="mc-dot"></span> Lab Tests</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.lab-tests.create') ? 'active' : '' }}" href="{{ route('admin.lab-tests.create') }}"><span class="mc-dot"></span> Add Test</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.lab-orders.*') ? 'active' : '' }}" href="{{ route('admin.lab-orders.index') }}"><span class="mc-dot"></span> Lab Orders</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}" href="{{ route('admin.invoices.index') }}"><span class="mc-dot"></span> Invoices</a></li>
                </ul>
            </li>

            <li class="mc-treeview {{ request()->routeIs('admin.bloodbank.*', 'admin.blood-groups.*', 'admin.blood-donors.*', 'admin.blood-donations.*', 'admin.blood-requests.*', 'admin.blood-issues.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('admin.bloodbank.*', 'admin.blood-groups.*', 'admin.blood-donors.*', 'admin.blood-donations.*', 'admin.blood-requests.*', 'admin.blood-issues.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-droplet"></i>
                    <span>Blood Bank</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="flex flex-col gap-0.5 py-1 pl-4">
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

            <li class="mc-treeview {{ request()->routeIs('settings.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-gear"></i>
                    <span>Settings</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="flex flex-col gap-0.5 py-1 pl-4">
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.general') ? 'active' : '' }}" href="{{ route('settings.general') }}"><span class="mc-dot"></span> General</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.home') ? 'active' : '' }}" href="{{ route('settings.home') }}"><span class="mc-dot"></span> Home</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.about') ? 'active' : '' }}" href="{{ route('settings.about') }}"><span class="mc-dot"></span> About</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.service') ? 'active' : '' }}" href="{{ route('settings.service') }}"><span class="mc-dot"></span> Service</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.doctor') ? 'active' : '' }}" href="{{ route('settings.doctor') }}"><span class="mc-dot"></span> Doctor</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.blog') ? 'active' : '' }}" href="{{ route('settings.blog') }}"><span class="mc-dot"></span> Blog</a></li>
                    <li><a class="mc-treeview-item {{ request()->routeIs('settings.contact') ? 'active' : '' }}" href="{{ route('settings.contact') }}"><span class="mc-dot"></span> Contact</a></li>
                </ul>
            </li>

            <a class="mc-nav__item {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}" href="{{ route('admin.activity-logs.index') }}">
                <i class="mc-nav-icon bi bi-list-check"></i>
                <span>Activity Logs</span>
            </a>

        @elseif(auth()->user()->role === 'doctor')

            <span class="mc-nav__group">Clinic</span>

            <a class="mc-nav__item {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}" href="{{ route('doctor.dashboard') }}">
                <i class="mc-nav-icon bi bi-speedometer"></i>
                <span>Dashboard</span>
            </a>

            <a class="mc-nav__item {{ request()->routeIs('doctor.appointments') ? 'active' : '' }}" href="{{ route('doctor.appointments') }}">
                <i class="mc-nav-icon bi bi-calendar-check"></i>
                <span>Appointments</span>
            </a>

            <a class="mc-nav__item {{ request()->routeIs('doctor.lab-orders.*') ? 'active' : '' }}" href="{{ route('doctor.lab-orders.index') }}">
                <i class="mc-nav-icon bi bi-clipboard2-pulse"></i>
                <span>Lab Requests</span>
            </a>

            <li class="mc-treeview {{ request()->routeIs('doctor.blood-requests.*') ? 'is-expanded' : '' }}">
                <a class="mc-nav__item {{ request()->routeIs('doctor.blood-requests.*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                    <i class="mc-nav-icon bi bi-droplet"></i>
                    <span>Blood Requests</span>
                    <i class="mc-chevron bi bi-chevron-right"></i>
                </a>
                <ul class="flex flex-col gap-0.5 py-1 pl-4">
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
</aside>