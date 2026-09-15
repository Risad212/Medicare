@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Settings</p>
        <h1 class="mc-title">General <em>Settings</em></h1>
        <p class="mc-sub">Site identity, header, and footer configuration.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<form action="{{ route('settings.general.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- SITE IDENTITY --}}
    <section class="mc-sec mb-4">
        <div class="mc-sec-hd"><span class="no">01</span><h3>Site Identity</h3><p>Logo, favicon, and name.</p></div>
        <div class="mc-sec-bd">
            <div class="mc-f full">
                <label>Site Name</label>
                <input type="text" name="site_name" class="form-control" value="{{ $setting->site_name ?? '' }}">
            </div>
            <div class="mc-f full">
                <label>Site Logo (200×60 px)</label>
                @if(!empty($setting->logo))
                    <img src="{{ asset('storage/'.$setting->logo) }}" class="mc-imgprev mb-2" alt="">
                @endif
                <input type="file" name="logo" class="form-control">
            </div>
            <div class="mc-f full">
                <label>Favicon (32×32 px, PNG or ICO)</label>
                @if(!empty($setting->favicon))
                    <img src="{{ asset('storage/'.$setting->favicon) }}" class="mb-2 h-8 w-8 rounded border border-line object-contain" alt="">
                @endif
                <input type="file" name="favicon" class="form-control">
            </div>
        </div>
    </section>

    {{-- HEADER --}}
    <section class="mc-sec mb-4">
        <div class="mc-sec-hd"><span class="no">02</span><h3>Header</h3><p>Address, hours, and social links.</p></div>
        <div class="mc-sec-bd">
            <div class="mc-f full">
                <label>Address</label>
                <textarea name="address" class="form-control" rows="3">{{ $setting->address ?? '' }}</textarea>
            </div>
            <div class="mc-f full">
                <label>Working Hours</label>
                <textarea name="working_hours" class="form-control" rows="3">{{ $setting->working_hours ?? '' }}</textarea>
            </div>
            <div class="mc-f">
                <label>Facebook</label>
                <input name="facebook" class="form-control" value="{{ $setting->facebook ?? '' }}">
            </div>
            <div class="mc-f">
                <label>Twitter / X</label>
                <input name="twitter" class="form-control" value="{{ $setting->twitter ?? '' }}">
            </div>
            <div class="mc-f">
                <label>LinkedIn</label>
                <input name="linkedin" class="form-control" value="{{ $setting->linkedin ?? '' }}">
            </div>
            <div class="mc-f">
                <label>YouTube</label>
                <input name="youtube" class="form-control" value="{{ $setting->youtube ?? '' }}">
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <section class="mc-sec mb-4">
        <div class="mc-sec-hd"><span class="no">03</span><h3>Footer</h3><p>Contact info and copyright.</p></div>
        <div class="mc-sec-bd">
            <div class="mc-f full">
                <label>Footer Logo</label>
                @if(!empty($setting->footer_logo))
                    <img src="{{ asset('storage/'.$setting->footer_logo) }}" class="mc-imgprev mb-2" alt="">
                @endif
                <input type="file" name="footer_logo" class="form-control">
            </div>
            <div class="mc-f">
                <label>Phone Number</label>
                <input type="text" name="phone" class="form-control" value="{{ $setting->phone ?? '' }}">
            </div>
            <div class="mc-f">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ $setting->email ?? '' }}">
            </div>
            <div class="mc-f full">
                <label>Short Description</label>
                <textarea name="footer_description" class="form-control" rows="4">{{ $setting->footer_description ?? '' }}</textarea>
            </div>
            <div class="mc-f full">
                <label>Copyright Text</label>
                <input name="copyright" class="form-control" value="{{ $setting->copyright ?? '' }}">
            </div>
        </div>
    </section>

    <div class="mc-formacts">
        <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Save general settings</button>
    </div>
</form>

@endsection
