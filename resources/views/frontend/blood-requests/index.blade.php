@extends('frontend.layouts.front-app')

@section('meta_title', 'My Blood Requests')
@section('meta_description', 'Track your blood requests and issued blood')
@section('meta_keywords', 'blood requests, blood bank, medicare')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => 'My Blood Requests'
])

<section class="patient-profile py-5">
    <div class="container">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">My Blood Requests</h3>
            <a href="{{ route('profile') }}" class="btn btn-outline-primary">&larr; Back to Profile</a>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Requests</h5>
            </div>
            <div class="card-body">

                @if($requests->count())

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Group</th>
                                    <th>Quantity</th>
                                    <th>Urgency</th>
                                    <th>Required Date</th>
                                    <th>Issued So Far</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($requests as $request)

                                    <tr>
                                        <td>#{{ $request->id }}</td>
                                        <td><strong>{{ $request->bloodGroup->name ?? 'N/A' }}</strong></td>
                                        <td>{{ $request->quantity }} {{ $request->unit }}</td>
                                        <td>
                                            <span class="badge {{ $request->urgency === 'emergency' ? 'bg-danger' : ($request->urgency === 'urgent' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                                {{ ucfirst($request->urgency) }}
                                            </span>
                                        </td>
                                        <td>{{ $request->required_date->format('d M Y') }}</td>
                                        <td>{{ $request->issuedQuantity() }} {{ $request->unit }}</td>
                                        <td>

                                            @if($request->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($request->status === 'approved')
                                                <span class="badge bg-info text-dark">Approved</span>
                                            @elseif($request->status === 'partially_approved')
                                                <span class="badge" style="background:#05d3b0;color:#fff">Partially Approved</span>
                                            @elseif($request->status === 'fulfilled')
                                                <span class="badge bg-success">Fulfilled</span>
                                            @elseif($request->status === 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-secondary">Cancelled</span>
                                            @endif

                                        </td>
                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                    <div class="d-flex justify-content-end">
                        {{ $requests->links() }}
                    </div>

                @else

                    <div class="text-center py-5">

                        <h5>
                            No Blood Requests Found
                        </h5>

                        <p class="text-muted">
                            When your doctor raises a blood request for you, it will appear here.
                        </p>

                    </div>

                @endif

            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Blood Issued to Me</h5>
            </div>
            <div class="card-body">

                @if($issues->count())

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>
                                <tr>
                                    <th>Group</th>
                                    <th>Quantity</th>
                                    <th>Issue Date</th>
                                    <th>Received By</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($issues as $issue)

                                    <tr>
                                        <td><strong>{{ $issue->bloodGroup->name ?? 'N/A' }}</strong></td>
                                        <td>{{ $issue->quantity }} {{ $issue->unit }}</td>
                                        <td>{{ $issue->issue_date->format('d M Y') }}</td>
                                        <td>{{ $issue->receiver_name ?: auth()->user()->name }}</td>
                                        <td>{{ $issue->notes ?: '—' }}</td>
                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                    <div class="d-flex justify-content-end">
                        {{ $issues->links() }}
                    </div>

                @else

                    <div class="text-center py-5">

                        <h5>
                            No Blood Issued Yet
                        </h5>

                        <p class="text-muted">
                            Blood issued against your requests will be listed here.
                        </p>

                    </div>

                @endif

            </div>
        </div>

    </div>
</section>

@endsection