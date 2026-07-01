@extends('frontend.layouts.front-app')

@section('front-content')

<div class="breadcrumb">
    <div class="container">
        <div class="col-xs-12">
            <div class="breadcrumb-content">
                <div class="breadcrumb-text-wrapper">
                    <h2 class="page-name">Doctor Details</h2>
                    <ul class="breadcrumb-list">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li>Doctor</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="doctor-details">
    <div class="container my-4">
        <div class="row">

            <div class="col-lg-4">
                <div class="doctor-details-image">
                    @if($doctor->image)
                        <img src="{{ asset('storage/'.$doctor->image) }}"
                             alt="{{ $doctor->name }}"
                             class="img-fluid">
                    @endif
                </div>
            </div>

            <div class="col-lg-8">
                <div class="doctor-info">

                    <h4 class="doctor-name">{{ $doctor->name }}</h4>

                    <h6 class="small">
                        <strong>Degree:</strong> {{ $doctor->degree }}
                    </h6>

                    <h5 class="small">
                        <strong>Department:</strong> {{ $doctor->department }}
                    </h5>

                    <h5 class="small mb-3">
                        <strong>Specialist:</strong> {{ $doctor->specialist }}
                    </h5>

                    <div class="mb-2">
                       <strong>Services:</strong>
                       {!! $doctor->services !!}
                   </div>

                    <h5 class="doctor-availability">
                        <strong>Availability:</strong> {{ $doctor->availability }}
                    </h5>

                    <h5 class="doctor-availability">
                        <strong>Phone:</strong> {{ $doctor->phone }}
                    </h5>

                </div>
            </div>

            <div class="col-lg-12" id="book-appoiment">
                <div class="book-appoiment mt-5">
                    <div class="appointment-here-form">

                        <a id="appointment"></a>

                        <h3>Make an Appointment</h3>

                        <form action="#">

                            <div class="row">

                                <div class="col-lg-6">
                                    <label>Doctor Name</label>
                                    <div class="form-group">
                                        <input type="text"
                                               class="form-control"
                                               readonly
                                               value="{{ $doctor->name }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label>Visit Type</label>
                                    <div class="form-group">
                                        <select id="visit_type" class="form-control" name="visit_type">
                                            <option value="1">First Visit</option>
                                            <option value="2">Second Visit</option>
                                            <option value="3">Report Review</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label>Patient Name</label>
                                    <div class="form-group">
                                        <input type="text"
                                               class="form-control"
                                               name="name"
                                               placeholder="Enter patient name">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label>Age</label>
                                    <div class="form-group">
                                        <input type="text"
                                               class="form-control"
                                               name="age"
                                               placeholder="Your age">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label>Gender</label>
                                    <div class="form-group">
                                        <select class="form-control" name="gender">
                                            <option disabled selected>Select Gender</option>
                                            <option value="1">Male</option>
                                            <option value="2">Female</option>
                                            <option value="3">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label>Phone</label>
                                    <div class="form-group">
                                        <input type="number"
                                               class="form-control"
                                               name="phone"
                                               placeholder="Your phone number">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label>Appointment Date</label>
                                    <div class="form-group">
                                        <input type="date"
                                               name="date"
                                               class="form-control appoiment-date">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label>Reference</label>
                                    <div class="form-group">
                                        <select class="form-control" name="ref_type">
                                            <option disabled selected>Reference</option>
                                            <option value="1">Self</option>
                                            <option value="2">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <input type="hidden" name="doctor_id" value="17">

                            </div>

                            <div class="border border-1 border-success p-3 mb-2 rounded"
                                 id="ref_person"
                                 style="display:none;">

                                <div class="mb-3 form-group">
                                    <input type="text"
                                           class="form-control"
                                           name="ref_other_name"
                                           placeholder="Reference Person Name">
                                </div>

                                <div class="mb-3 form-group">
                                    <input type="text"
                                           class="form-control"
                                           name="ref_other_phone"
                                           placeholder="Reference Person Phone">
                                </div>

                                <div class="mb-0 form-group">
                                    <input type="text"
                                           class="form-control"
                                           name="ref_other_address"
                                           placeholder="Reference Person Address">
                                </div>

                            </div>

                            <div class="col-12">
                                <button type="submit" class="default-btn">
                                    Send Request
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@endsection