@extends('frontend.layouts.front-app')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => 'Appoinment'
])

<!--========== Appoinment Section ==========-->
<section class="appoinment-page">
    <div class="container">
        <div class="form-wrap">
            <div class="mb-4">
                <input type="text" id="appoinment_name" placeholder="Your Name">
            </div>
            <div class="mb-4">
                <input type="email" id="appoinment_email" placeholder="Your Email">
            </div>
            <div class="mb-4">
                <input type="number" id="appoinment_number" placeholder="Your Number">
            </div>
            <div class="mb-4">
                <select name="doctor" id="doctors">
                    <option selected>select doctor</option>
                    <option value="#">doctor 1</option>
                    <option value="#">doctor 2</option>
                    <option value="#">doctor 3</option>
                    <option value="#">doctor 4</option>
                    <option value="#">doctor 5</option>
                </select>
            </div>
            <div class="mb-4">
                <input type="text" name="txtDate" data-date-format="dd-mm-yyyy" id="dateTo" placeholder="date"
                    required="">
            </div>
            <div class="mb-4">
                <select name="time" id="time">
                    <option selected>Time</option>
                    <option value="#">11: 00 AM</option>
                    <option value="#">11: 00 AM</option>
                    <option value="#">12: 00 PM</option>
                    <option value="#">01: 00 PM</option>
                    <option value="#">02: 00 PM</option>
                </select>
            </div>
            <div class="text-center">
                <button class="appoinment-btn">submit</button>
            </div>
        </div>
    </div>
</section>

@endsection