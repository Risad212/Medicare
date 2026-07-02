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

        <div class="pagination-wrap">
    <ul class="pagination-list">

        {{-- Previous --}}
        <li class="page-item {{ $doctors->onFirstPage() ? 'disabled' : '' }}">
            <a href="{{ $doctors->previousPageUrl() }}">
                <svg fill="#000000" viewBox="-9.5 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_iconCarrier">
                        <path d="M7.28 23.28c-0.2 0-0.44-0.080-0.6-0.24l-6.44-6.44c-0.32-0.32-0.32-0.84 0-1.2l6.44-6.44c0.32-0.32 0.84-0.32 1.2 0 0.32 0.32 0.32 0.84 0 1.2l-5.84 5.84 5.84 5.84c0.32 0.32 0.32 0.84 0 1.2-0.16 0.16-0.4 0.24-0.6 0.24zM12.040 23.28c-0.2 0-0.44-0.080-0.6-0.24l-6.44-6.44c-0.32-0.32-0.32-0.84 0-1.2l6.44-6.44c0.32-0.32 0.84-0.32 1.2 0 0.32 0.32 0.32 0.84 0 1.2l-5.88 5.84 5.84 5.84c0.32 0.32 0.32 0.84 0 1.2-0.12 0.16-0.36 0.24-0.56 0.24z"></path>
                    </g>
                </svg>
            </a>
        </li>

        {{-- Page Numbers --}}
        @foreach($doctors->getUrlRange(1, $doctors->lastPage()) as $page => $url)
        <li class="page-item {{ $doctors->currentPage() == $page ? 'active' : '' }}">
            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
        </li>
        @endforeach

        {{-- Next --}}
        <li class="page-item {{ !$doctors->hasMorePages() ? 'disabled' : '' }}">
            <a href="{{ $doctors->nextPageUrl() }}">
                <svg viewBox="-9.5 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_iconCarrier">
                        <path d="M5.6 23.28c-0.2 0-0.44-0.080-0.6-0.24-0.32-0.32-0.32-0.84 0-1.2l5.84-5.84-5.84-5.84c-0.32-0.32-0.32-0.84 0-1.2 0.32-0.32 0.84-0.32 1.2 0l6.44 6.44c0.16 0.16 0.24 0.36 0.24 0.6s-0.080 0.44-0.24 0.6l-6.44 6.44c-0.16 0.16-0.4 0.24-0.6 0.24zM0.84 23.28c-0.2 0-0.44-0.080-0.6-0.24-0.32-0.32-0.32-0.84 0-1.2l5.84-5.84-5.84-5.84c-0.32-0.32-0.32-0.84 0-1.2 0.32-0.32 0.84-0.32 1.2 0l6.44 6.44c0.16 0.16 0.24 0.36 0.24 0.6s-0.080 0.44-0.24 0.6l-6.44 6.44c-0.16 0.16-0.4 0.24-0.6 0.24z"></path>
                    </g>
                </svg>
            </a>
        </li>

    </ul>
</div>
    </div>
</section>

@endsection