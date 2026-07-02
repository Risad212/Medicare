@extends('frontend.layouts.front-app')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => 'About Us'
])

<!--========== About Hospital section ==========-->
<div class="about-hospital">
    <div class="container">
        <div class="row">

            <div class="col-lg-6">
                <div class="about-image">

                    <div class="circle">
                        <img src="{{ asset('frontend-assets/media/about/circle.png') }}" alt="">
                    </div>

                    <div class="first-img">
                        <img class="img-fluid"
                            src="{{ asset('frontend-assets/media/about/about-1.jpg') }}"
                            alt="">
                    </div>

                    <div class="second-img">
                        <img class="img-fluid"
                            src="{{ asset('frontend-assets/media/about/about-2.jpg') }}"
                            alt="">
                    </div>

                </div>
            </div>

            <div class="col-lg-6">
                <div class="about-content">

                    <h6 class="subtitle">about us</h6>

                    <h2 class="title">
                        Compassionate Care Exceptional Expertise
                    </h2>

                    <span class="text">
                        The field where technology meets humanity
                    </span>

                    <p>
                        Lorem ipsum dolor sit amet consectetur adipisicing elit.
                        Saepe perferendis consectetur quis deleniti, at debitis
                        earum illo tempore molestias in eaque, architecto
                        reiciendis assumenda sapiente? Sit autem suscipit qui
                        tenetur!
                    </p>

                    <button class="about-btn">
                        <a href="#">more info</a>

                        <svg xmlns="http://www.w3.org/2000/svg"
                            version="1.1"
                            xmlns:xlink="http://www.w3.org/1999/xlink"
                            width="20"
                            height="20"
                            viewBox="0 0 512 512">

                            <g>
                                <path
                                    d="m506.134 241.843-.018-.019-104.504-104c-7.829-7.791-20.492-7.762-28.285.068-7.792 7.829-7.762 20.492.067 28.284L443.558 236H20c-11.046 0-20 8.954-20 20s8.954 20 20 20h423.557l-70.162 69.824c-7.829 7.792-7.859 20.455-.067 28.284 7.793 7.831 20.457 7.858 28.285.068l104.504-104 .018-.019c7.833-7.818 7.808-20.522-.001-28.314z">
                                </path>
                            </g>

                        </svg>
                    </button>

                </div>
            </div>

        </div>
    </div>
</div>

<!--========== Our Vission Section  ==========-->
<section class="our-vission">
    <div class="container">
        <div class="row">

            <div class="col-lg-4">
                <div class="single-vission-box">

                    <div class="icon">
                        <svg viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">

                            <g stroke-width="0"></g>
                            <g stroke-linecap="round"
                                stroke-linejoin="round"></g>

                            <g>
                                <path
                                    d="M4.89163 13.2687L9.16582 17.5427L18.7085 8"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">
                                </path>
                            </g>

                        </svg>
                    </div>

                    <h4 class="title">Our Mission</h4>

                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit,
                        sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                        Quis ipsum suspendisse.
                    </p>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="single-vission-box">

                    <div class="icon">
                        <svg viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">

                            <g stroke-width="0"></g>
                            <g stroke-linecap="round"
                                stroke-linejoin="round"></g>

                            <g>
                                <path
                                    d="M4.89163 13.2687L9.16582 17.5427L18.7085 8"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">
                                </path>
                            </g>

                        </svg>
                    </div>

                    <h4 class="title">Our Planning</h4>

                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit,
                        sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                        Quis ipsum suspendisse.
                    </p>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="single-vission-box">

                    <div class="icon">
                        <svg viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">

                            <g stroke-width="0"></g>
                            <g stroke-linecap="round"
                                stroke-linejoin="round"></g>

                            <g>
                                <path
                                    d="M4.89163 13.2687L9.16582 17.5427L18.7085 8"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round">
                                </path>
                            </g>

                        </svg>
                    </div>

                    <h4 class="title">Our Vision</h4>

                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit,
                        sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                        Quis ipsum suspendisse.
                    </p>

                </div>
            </div>

        </div>
    </div>
</section>

<!--============== TESTIMONIAL SECTION ===============-->
@include('frontend.components.testimonial')

<!--============== DOCTORS SECTION ===============-->
@include('frontend.components.doctor')

@endsection