@extends('frontend.layouts.front-app')

@section('meta_title', 'My Profile')
@section('meta_description', 'Manage your patient profile')
@section('meta_keywords', 'patient profile, appointments, medicare')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => 'My Profile'
])

<style>
:root{--mx-brand:#05d3b0;--mx-brand-dark:#049f84;--mx-ink:#0b1526;--mx-muted:#67748e;--mx-line:#e8eef5;--mx-bg:#f4f7fb;--mx-card-shadow:0 12px 32px -12px rgba(11,21,38,.14)}
.mx-wrap{background:var(--mx-bg);font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:var(--mx-ink)}
.mx-cover{border-radius:24px;background:radial-gradient(600px 220px at 12% 10%,rgba(255,255,255,.35),transparent 60%),radial-gradient(500px 260px at 88% 20%,rgba(14,165,233,.45),transparent 60%),radial-gradient(700px 300px at 55% 110%,rgba(5,211,176,.5),transparent 62%),linear-gradient(120deg,#042f2e 0%,#065f46 38%,#0e9f8a 68%,#22d3ee 100%);position:relative;overflow:hidden;min-height:200px}
.mx-cover-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.09) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.09) 1px,transparent 1px);background-size:34px 34px;mask-image:radial-gradient(80% 100% at 50% 0%,#000 55%,transparent 100%)}
.mx-cover-inner{position:relative;z-index:1;padding:1.6rem 1.6rem 4.2rem;color:#fff}
.mx-crumb{font-size:.75rem;letter-spacing:.08em;text-transform:uppercase;opacity:.75;font-weight:600}
.mx-cover h2{font-size:1.55rem;font-weight:750;letter-spacing:-.02em;margin:.3rem 0 .2rem}
.mx-cover p{opacity:.82;font-size:.9rem;margin:0}
.mx-profile-card{margin-top:-56px;position:relative;z-index:2}
.mx-card{background:#fff;border:1px solid var(--mx-line);border-radius:20px;box-shadow:var(--mx-card-shadow)}
.mx-avatar{width:104px;height:104px;border-radius:28px;object-fit:cover;border:4px solid #fff;box-shadow:0 12px 28px -8px rgba(11,21,38,.35);background:#e6fbf6}
.mx-avatar-fallback{width:104px;height:104px;border-radius:28px;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;color:#fff;background:linear-gradient(135deg,#05d3b0,#0ea5e9);border:4px solid #fff;box-shadow:0 12px 28px -8px rgba(11,21,38,.35)}
.mx-verified{display:inline-flex;align-items:center;gap:.35rem;font-size:.75rem;font-weight:700;color:#047857;background:#d1fae5;border:1px solid #a7f3d0;border-radius:999px;padding:.2rem .65rem}
.mx-id-chip{font-size:.75rem;color:var(--mx-muted);background:#f1f5f9;border:1px solid var(--mx-line);border-radius:999px;padding:.25rem .7rem;font-weight:600}
.mx-btn-primary{background:linear-gradient(135deg,#05d3b0,#0ea5e9);border:0;color:#fff;font-weight:700;font-size:.86rem;border-radius:14px;padding:.65rem 1.15rem;box-shadow:0 10px 22px -8px rgba(5,211,176,.65);transition:.2s}
.mx-btn-primary:hover{transform:translateY(-1px);color:#fff;box-shadow:0 14px 26px -8px rgba(5,211,176,.7)}
.mx-btn-ghost{background:#fff;border:1px solid var(--mx-line);color:var(--mx-ink);font-weight:600;font-size:.86rem;border-radius:14px;padding:.65rem 1.1rem;transition:.2s}
.mx-btn-ghost:hover{border-color:#05d3b0;color:#047857;background:#f0fdfa}
.mx-stat{background:#fff;border:1px solid var(--mx-line);border-radius:18px;padding:1rem;box-shadow:var(--mx-card-shadow);transition:.2s;position:relative;overflow:hidden}
.mx-stat:hover{transform:translateY(-2px)}
.mx-stat::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(180deg,var(--mx-brand),#0ea5e9);opacity:.9}
.mx-ico{width:42px;height:42px;border-radius:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.mx-ico svg{width:20px;height:20px}
.mx-stat .n{font-size:1.35rem;font-weight:800;letter-spacing:-.02em;line-height:1}
.mx-stat .t{font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--mx-muted);margin-top:.3rem}
.mx-nav{background:#fff;border:1px solid var(--mx-line);border-radius:18px;padding:.6rem;box-shadow:var(--mx-card-shadow);position:sticky;top:1rem}
.mx-nav .nav-link{display:flex;align-items:center;gap:.65rem;border-radius:13px;color:#334155;font-weight:600;font-size:.88rem;padding:.7rem .8rem;border:0;width:100%;text-align:left;transition:.18s;background:transparent}
.mx-nav .nav-link small{display:block;font-weight:500;color:var(--mx-muted);font-size:.75rem}
.mx-nav .nav-link:hover{background:#f8fafc}
.mx-nav .nav-link.active{background:linear-gradient(135deg,#ecfdf5,#eff6ff);color:#065f46;box-shadow:inset 0 0 0 1px #a7f3d0}
.mx-nav .dot{width:34px;height:34px;border-radius:11px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:800;color:#64748b}
.mx-nav .active .dot{background:#065f46;color:#fff}
.mx-nav .cnt{margin-left:auto;background:#f1f5f9;color:#475569;font-size:.72rem;font-weight:700;border-radius:999px;padding:.15rem .6rem}
.mx-nav .active .cnt{background:#065f46;color:#fff}
.mx-panel{background:#fff;border:1px solid var(--mx-line);border-radius:20px;box-shadow:var(--mx-card-shadow);overflow:hidden}
.mx-panel-head{padding:1.2rem 1.4rem .4rem}
.mx-panel-head h5{margin:0;font-size:1.02rem;font-weight:750;letter-spacing:-.01em}
.mx-panel-head p{margin:.2rem 0 0;color:var(--mx-muted);font-size:.85rem}
.mx-panel-body{padding:1.2rem 1.4rem 1.4rem}
.mx-field label{font-size:.78rem;font-weight:700;color:#334155;margin-bottom:.35rem;letter-spacing:.01em}
.mx-field .form-control,.mx-field .form-select{border-radius:12px;border:1px solid var(--mx-line);background:#f8fafc;font-size:.88rem;padding:.62rem .8rem}
.mx-field .form-control:focus,.mx-field .form-select:focus{background:#fff;border-color:var(--mx-brand);box-shadow:0 0 0 4px rgba(5,211,176,.15)}
.mx-table{margin:0;font-size:.87rem}
.mx-table thead th{font-size:.68rem;text-transform:uppercase;letter-spacing:.08em;color:#8a97ab;font-weight:700;border:0;background:#f8fafc;padding:.7rem 1rem;white-space:nowrap}
.mx-table tbody td{padding:.85rem 1rem;border-top:1px solid #f1f5f9;vertical-align:middle}
.mx-table tbody tr{transition:.15s}
.mx-table tbody tr:hover{background:#f8fffd}
.pill{display:inline-flex;align-items:center;gap:.4rem;font-size:.74rem;font-weight:700;border-radius:999px;padding:.3rem .7rem;white-space:nowrap;border:1px solid transparent}
.pill i{width:7px;height:7px;border-radius:50%;background:currentColor;display:inline-block}
.p-pending{background:#fefce8;color:#a16207;border-color:#fde68a}
.p-confirmed{background:#ecfdf5;color:#047857;border-color:#a7f3d0}
.p-completed{background:#eef2ff;color:#4338ca;border-color:#c7d2fe}
.p-cancelled{background:#f8fafc;color:#64748b;border-color:#e2e8f0}
.p-progress{background:#f0f9ff;color:#0369a1;border-color:#bae6fd}
.mx-doc{border:1px solid var(--mx-line);border-radius:16px;padding:.9rem 1rem;background:linear-gradient(180deg,#fff,#f8fffd)}
.mx-empty{padding:3rem 1.5rem;text-align:center}
.mx-empty-art{width:64px;height:64px;border-radius:20px;margin:0 auto 1rem;background:linear-gradient(135deg,#ecfdf5,#eff6ff);border:1px solid var(--mx-line);display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#047857;font-weight:800}
@media(max-width:991px){.mx-profile-card{margin-top:-40px}.mx-nav{position:static}.mx-cover-inner{padding-bottom:4.6rem}}
</style>

<section class="mx-wrap py-4 py-md-5">
<div class="container" style="max-width:1180px">

    @if(session('success'))
        <div class="alert alert-success shadow-sm border-0" style="border-radius:14px">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger shadow-sm border-0" style="border-radius:14px">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Cover --}}
    <div class="mx-cover mb-0">
        <div class="mx-cover-grid"></div>
        <div class="mx-cover-inner d-flex justify-content-between align-items-end flex-wrap gap-2">
            <div>
                <div class="mx-crumb">Patient portal</div>
                <h2>Good day, {{ explode(' ', trim($user->name))[0] ?? $user->name }}</h2>
                <p>Track visits, reports, prescriptions and billing in one place.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('appointment') }}" class="btn mx-btn-primary" style="background:#fff;color:#065f46;box-shadow:none">+ Book visit</a>
                <a href="{{ route('notifications.index') }}" class="btn btn-sm align-self-center text-white border border-white border-opacity-25" style="border-radius:12px">Notifications</a>
            </div>
        </div>
    </div>

    {{-- Profile card --}}
    <div class="mx-profile-card mb-4">
        <div class="mx-card p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center">
                @if($user->profile_image)
                    <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="mx-avatar">
                @else
                    <div class="mx-avatar-fallback">{{ $user->name ? strtoupper(mb_substr($user->name, 0, 1)) : '?' }}</div>
                @endif
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h4 class="mb-0" style="font-weight:800;letter-spacing:-.02em">{{ $user->name }}</h4>
                        <span class="mx-verified">Verified patient</span>
                    </div>
                    <div class="mt-1" style="color:var(--mx-muted);font-size:.88rem">{{ $user->email }} @if($user->phone)&middot; {{ $user->phone }}@endif</div>
                    <div class="d-flex gap-2 mt-2 flex-wrap">
                        <span class="mx-id-chip">ID #{{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }}</span>
                        <span class="mx-id-chip">Blood {{ $user->blood_group ?? '—' }}</span>
                        @if($user->created_at)<span class="mx-id-chip">Since {{ $user->created_at->format('M Y') }}</span>@endif
                    </div>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                    <a href="{{ route('appointment') }}" class="btn mx-btn-primary">Book appointment</a>
                    <a href="{{ route('profile.blood-requests') }}" class="btn mx-btn-ghost">Blood requests</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="mx-stat d-flex gap-3 align-items-center">
                <div class="mx-ico" style="background:#ecfdf5;color:#047857"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
                <div><div class="n">{{ $appointments->count() }}</div><div class="t">Appointments</div></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="mx-stat d-flex gap-3 align-items-center">
                <div class="mx-ico" style="background:#fefce8;color:#a16207"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></div>
                <div><div class="n">{{ $appointments->where('status', 0)->count() }}</div><div class="t">Pending</div></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="mx-stat d-flex gap-3 align-items-center">
                <div class="mx-ico" style="background:#eff6ff;color:#4338ca"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3h6v4H9zM7 5H5a2 2 0 0 0-2 2v14h18V7a2 2 0 0 0-2-2h-2"/><path d="M9 13h6M9 17h4"/></svg></div>
                <div><div class="n">{{ $prescriptions->count() }}</div><div class="t">Prescriptions</div></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="mx-stat d-flex gap-3 align-items-center">
                <div class="mx-ico" style="background:#f0f9ff;color:#0369a1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 3h6M10 3v6L4.5 19a2 2 0 0 0 1.8 3h11.4a2 2 0 0 0 1.8-3L14 9V3"/><path d="M7 15h10"/></svg></div>
                <div><div class="n">{{ $labOrders->count() }}</div><div class="t">Lab orders</div></div>
            </div>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-lg-3">
            <div class="mx-nav nav flex-column gap-1" id="profileTabs" role="tablist">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab"><span class="dot">U</span><span>Profile<small>Personal details</small></span></button>
                <button class="nav-link" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab"><span class="dot">A</span><span>Appointments<small>Visits &amp; status</small></span><span class="cnt">{{ $appointments->count() }}</span></button>
                <button class="nav-link" id="lab-tab" data-bs-toggle="tab" data-bs-target="#lab-reports" type="button" role="tab"><span class="dot">L</span><span>Lab reports<small>Orders &amp; results</small></span><span class="cnt">{{ $labOrders->count() }}</span></button>
                <button class="nav-link" id="prescriptions-tab" data-bs-toggle="tab" data-bs-target="#prescriptions" type="button" role="tab"><span class="dot">R</span><span>Prescriptions<small>Medicines</small></span><span class="cnt">{{ $prescriptions->count() }}</span></button>
                <button class="nav-link" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab"><span class="dot">$</span><span>Invoices<small>Billing</small></span><span class="cnt">{{ $invoices->count() }}</span></button>
                <hr class="my-2">
                <form action="{{ route('logout') }}" method="POST" class="px-1 pb-1 d-grid">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:12px">Sign out</button>
                </form>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="tab-content" id="profileTabsContent">

                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <div class="mx-panel">
                        <div class="mx-panel-head"><h5>Personal information</h5><p>Used for booking, reminders and medical records.</p></div>
                        <div class="mx-panel-body">
                            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-6 mx-field"><label>Full name</label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                    <div class="col-md-6 mx-field"><label>Email</label><input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                    <div class="col-md-6 mx-field"><label>Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="+880 1XXX XXXXXX"></div>
                                    <div class="col-md-6 mx-field"><label>Date of birth</label><input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"></div>
                                    <div class="col-md-6 mx-field"><label>Gender</label>
                                        <select name="gender" class="form-select"><option value="">Select</option><option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male</option><option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option><option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Other</option></select>
                                    </div>
                                    <div class="col-md-6 mx-field"><label>Blood group</label>
                                        <select name="blood_group" class="form-select"><option value="">Select</option>@foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group)<option value="{{ $group }}" {{ old('blood_group', $user->blood_group) === $group ? 'selected' : '' }}>{{ $group }}</option>@endforeach</select>
                                    </div>
                                    <div class="col-12 mx-field"><label>Address</label><textarea name="address" rows="3" class="form-control" placeholder="House, road, area">{{ old('address', $user->address) }}</textarea></div>
                                    <div class="col-12 mx-field"><label>Profile photo</label><input type="file" name="profile_image" class="form-control" accept="image/jpeg,image/png,image/webp"><small class="text-muted">JPG, PNG or WEBP, max 2MB.</small></div>
                                </div>
                                <div class="d-flex gap-2 mt-4"><button type="submit" class="btn mx-btn-primary px-4">Save changes</button></div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="appointments" role="tabpanel">
                    <div class="mx-panel">
                        <div class="mx-panel-head d-flex justify-content-between align-items-center"><div><h5>Appointments</h5><p>{{ $appointments->where('status', 0)->count() }} pending &middot; {{ $appointments->count() }} total</p></div><a href="{{ route('appointment') }}" class="btn mx-btn-primary btn-sm">New booking</a></div>
                        @if($appointments->count())
                            <div class="table-responsive"><table class="table mx-table mb-0">
                                <thead><tr><th>Doctor</th><th>Date</th><th>Time</th><th>Type</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                                <tbody>
                                @foreach($appointments as $appointment)
                                    <tr>
                                        <td class="fw-semibold text-nowrap">{{ $appointment->doctor->name ?? 'N/A' }}</td>
                                        <td class="text-nowrap">{{ $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') : 'N/A' }}</td>
                                        <td class="text-nowrap">{{ $appointment->timeSlot->time ?? 'N/A' }}</td>
                                        <td>@if($appointment->visit_type == 1)First visit@elseif($appointment->visit_type == 2)Second visit@elseif($appointment->visit_type == 3)Report review@else<span class="text-muted">N/A</span>@endif</td>
                                        <td>@if($appointment->status == 1)<span class="pill p-confirmed"><i></i>Confirmed</span>@elseif($appointment->status == 2)<span class="pill p-completed"><i></i>Completed</span>@elseif($appointment->status == 3)<span class="pill p-cancelled"><i></i>Cancelled</span>@else<span class="pill p-pending"><i></i>Pending</span>@endif</td>
                                        <td class="text-end">@if($appointment->status == 0)<form action="{{ route('appointment.cancel', $appointment->id) }}" method="POST" class="d-inline">@csrf @method('PATCH')<button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:10px" onclick="return confirm('Cancel this appointment?')">Cancel</button></form>@else<span class="text-muted">—</span>@endif</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table></div>
                        @else
                            <div class="mx-empty"><div class="mx-empty-art">A</div><h6 class="fw-bold">No appointments yet</h6><p class="text-muted small mb-3">Your bookings will show here with live status.</p><a href="{{ route('appointment') }}" class="btn mx-btn-primary btn-sm">Book appointment</a></div>
                        @endif
                    </div>
                </div>

                <div class="tab-pane fade" id="lab-reports" role="tabpanel">
                    @if($labOrders->count())
                        @foreach($labOrders as $labOrder)
                            <div class="mx-panel mb-3">
                                <div class="mx-panel-head d-flex justify-content-between align-items-center"><div><h5>Order #{{ $labOrder->id }}</h5><p>{{ $labOrder->created_at->format('d M Y') }} &middot; {{ $labOrder->doctor->name ?? 'N/A' }}</p></div>@if($labOrder->status === 'pending')<span class="pill p-pending"><i></i>Pending</span>@elseif($labOrder->status === 'in-progress')<span class="pill p-progress"><i></i>In progress</span>@elseif($labOrder->status === 'completed')<span class="pill p-confirmed"><i></i>Completed</span>@else<span class="pill p-cancelled"><i></i>Cancelled</span>@endif</div>
                                <div class="mx-panel-body pt-2">
                                    @if($labOrder->note)<p class="text-muted small mb-2">{{ $labOrder->note }}</p>@endif
                                    <div class="mb-3">@foreach($labOrder->items as $item)<span class="badge bg-light text-dark border me-1 mb-1" style="border-radius:999px">{{ $item->test->name ?? 'Removed test' }}</span>@endforeach</div>
                                    <div class="d-flex justify-content-end mb-2"><a href="{{ route('profile.lab-orders.pdf', $labOrder->id) }}" class="btn btn-sm mx-btn-ghost">Order PDF</a></div>
                                    @if($labOrder->reports->count())
                                        @foreach($labOrder->reports as $report)
                                            <div class="mx-doc d-flex justify-content-between align-items-center gap-2 mb-2"><div><div class="fw-semibold small">{{ $report->report_name }}</div>@if($report->notes)<div class="text-muted small">{{ $report->notes }}</div>@endif</div><a href="{{ route('profile.lab-reports.download', $report->id) }}" class="btn btn-sm mx-btn-primary">Download</a></div>
                                        @endforeach
                                    @else
                                        <p class="text-muted small mb-0">Results not available yet.</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="mx-panel"><div class="mx-empty"><div class="mx-empty-art">L</div><h6 class="fw-bold">No lab reports</h6><p class="text-muted small mb-0">Ordered tests and results will appear here.</p></div></div>
                    @endif
                </div>

                <div class="tab-pane fade" id="prescriptions" role="tabpanel">
                    @if($prescriptions->count())
                        @foreach($prescriptions as $prescription)
                            <div class="mx-panel mb-3">
                                <div class="mx-panel-head d-flex justify-content-between align-items-center"><div><h5>Prescription #{{ $prescription->id }}</h5><p>Dr. {{ $prescription->doctor->name ?? 'N/A' }} &middot; {{ $prescription->created_at->format('d M Y') }}</p></div><a href="{{ route('profile.prescriptions.pdf', $prescription->id) }}" class="btn btn-sm mx-btn-ghost">PDF</a></div>
                                <div class="mx-panel-body pt-2">
                                    <p class="small mb-2"><strong>Diagnosis:</strong> {{ $prescription->diagnosis }}</p>
                                    @if($prescription->items->count())
                                        <div class="table-responsive"><table class="table mx-table table-sm mb-2"><thead><tr><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Qty</th><th>Instructions</th></tr></thead><tbody>@foreach($prescription->items as $item)<tr><td class="fw-semibold">{{ $item->medicine_name }}</td><td>{{ $item->dosage ?? '—' }}</td><td>{{ $item->frequency ?? '—' }}</td><td>{{ $item->duration ?? '—' }}</td><td>{{ $item->quantity ?? '—' }}</td><td>{{ $item->instructions ?? '—' }}</td></tr>@endforeach</tbody></table></div>
                                    @endif
                                    @if($prescription->advice)<p class="text-muted small mb-0"><strong>Advice:</strong> {{ $prescription->advice }}</p>@endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="mx-panel"><div class="mx-empty"><div class="mx-empty-art">R</div><h6 class="fw-bold">No prescriptions</h6><p class="text-muted small mb-0">Prescriptions from your doctors will appear here.</p></div></div>
                    @endif
                </div>

                <div class="tab-pane fade" id="invoices" role="tabpanel">
                    <div class="mx-panel">
                        <div class="mx-panel-head"><h5>Invoices</h5><p>Billing history for lab services.</p></div>
                        @if($invoices->count())
                            <div class="table-responsive"><table class="table mx-table mb-0"><thead><tr><th>Invoice no</th><th>Date</th><th>Total</th><th>Status</th></tr></thead><tbody>@foreach($invoices as $invoice)<tr><td class="fw-semibold">{{ $invoice->invoice_no }}</td><td>{{ $invoice->created_at->format('d M Y') }}</td><td>{{ $invoice->total }}</td><td>@if($invoice->status === 'paid')<span class="pill p-confirmed"><i></i>Paid</span>@elseif($invoice->status === 'void')<span class="pill p-cancelled"><i></i>Void</span>@else<span class="pill p-pending"><i></i>Pending</span>@endif</td></tr>@endforeach</tbody></table></div>
                        @else
                            <div class="mx-empty"><div class="mx-empty-art">$</div><h6 class="fw-bold">No invoices</h6><p class="text-muted small mb-0">Invoices created for you will appear here.</p></div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</section>

@endsection
