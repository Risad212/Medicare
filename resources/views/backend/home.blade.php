@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="widget-small primary coloured-icon">
            <i class="icon bi bi-people fs-1"></i>
            <div class="info">
                <h4>Doctors</h4>
                <p><b>{{ $totalDoctors }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small info coloured-icon">
            <i class="icon bi bi-calendar-check fs-1"></i>
            <div class="info">
                <h4>Appointments</h4>
                <p><b>{{ $totalAppointments }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small warning coloured-icon">
            <i class="icon bi bi-journal-text fs-1"></i>
            <div class="info">
                <h4>Blog Posts</h4>
                <p><b>{{ $totalBlogs }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="widget-small danger coloured-icon">
            <i class="icon bi bi-chat-dots fs-1"></i>
            <div class="info">
                <h4>Pending Comments</h4>
                <p><b>{{ $pendingComments }}</b></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="tile">
            <h3 class="tile-title">Weekly Sales - Last week</h3>
            <div class="ratio ratio-16x9">
                <div id="salesChart"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="tile">
            <h3 class="tile-title">Support Requests</h3>
            <div class="ratio ratio-16x9">
                <div id="supportRequestChart"></div>
            </div>
        </div>
    </div>
</div>
@endsection