@extends('frontend.layouts.front-app')

@section('meta_title', 'Request Ambulance')
@section('meta_description', 'Urgent ambulance request — pickup and emergency details')
@section('meta_keywords', 'ambulance, emergency, medicare')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => 'Request Ambulance'
])

<section class="contact-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0" style="border-radius:18px">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="mb-1 fw-bold">Urgent ambulance request</h2>
                        <p class="text-muted mb-4">Fill the minimum details — our team calls back immediately. No login needed.</p>

                        @if(session('success'))
                            <div class="alert alert-success" style="border-radius:12px">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger" style="border-radius:12px">
                                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                            </div>
                        @endif

                        <form action="{{ route('ambulance.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Your name <span class="text-danger">*</span></label>
                                    <input type="text" name="requester_name" class="form-control @error('requester_name') is-invalid @enderror" value="{{ old('requester_name', auth()->user?->name) }}" placeholder="Full name" required>
                                    @error('requester_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                                    <input type="tel" name="requester_phone" class="form-control @error('requester_phone') is-invalid @enderror" value="{{ old('requester_phone', auth()->user?->phone) }}" placeholder="+880 1XXX XXXXXX" required>
                                    @error('requester_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Pickup address <span class="text-danger">*</span></label>
                                    <textarea name="pickup_address" rows="3" class="form-control @error('pickup_address') is-invalid @enderror" placeholder="House, road, area, landmark" required>{{ old('pickup_address') }}</textarea>
                                    @error('pickup_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Destination (optional)</label>
                                    <input type="text" name="destination" class="form-control" value="{{ old('destination') }}" placeholder="This hospital or another facility">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Emergency type (optional)</label>
                                    <input type="text" name="emergency_type" class="form-control" value="{{ old('emergency_type') }}" placeholder="e.g. Accident, labor pain">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-danger btn-lg w-100 mt-4" style="border-radius:12px">Request ambulance now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
