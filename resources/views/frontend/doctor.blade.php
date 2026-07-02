@extends('frontend.layouts.front-app')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => 'Our Doctors'
])

<!--========== Doctors Section ==========-->
<section class="doctor-section doctor-page">
    <div class="container">

        <div class="row gy-4">

            @forelse($doctors as $doctor)

            <div class="col-lg-3">
                <div class="doctor-card">

                    <div class="card-img">
                        <img class="img-fluid"
                             src="{{ asset('storage/'.$doctor->image) }}"
                             alt="{{ $doctor->name }}">
                    </div>

                    <div class="card-content">

                        <h4 class="title">
                            {{ $doctor->name }}
                        </h4>

                        <span class="speacility">
                            {{ $doctor->specialist }}
                        </span>

                        <a href="{{ route('doctor.show', $doctor->id) }}" class="card-btn">
                            Book Appointment
                        </a>

                    </div>

                </div>
            </div>

            @empty

            <p>No doctors found</p>

            @endforelse

        </div>

    </div>
</section>

@endsection