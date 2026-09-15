@extends('frontend.layouts.front-app')

@section('meta_title', 'Cancel Appointment')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => 'Cancel Appointment'
])

<section class="mc-cancel-wrapper">

    @if(session('success'))
        <div class="mc-cancel-alert mc-cancel-alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mc-cancel-alert mc-cancel-alert-error">{{ session('error') }}</div>
    @endif

    <div class="mc-cancel-card">

        <div class="mc-cancel-header">
            <h2 class="mc-cancel-header-title">Appointment Details</h2>
        </div>

        <div class="mc-cancel-body">

            <p class="mc-cancel-lead">
                Hello {{ $appointment->patient_name }}, here are your appointment details:
            </p>

            <table class="mc-cancel-table">
                <tr>
                    <td class="mc-cancel-table-label">Doctor:</td>
                    <td>{{ $appointment->doctor->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="mc-cancel-table-label">Date:</td>
                    <td>{{ $appointment->appointment_date }}</td>
                </tr>
                <tr>
                    <td class="mc-cancel-table-label">Time:</td>
                    <td>{{ $appointment->timeSlot->time ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="mc-cancel-table-label">Visit Type:</td>
                    <td>{{ $appointment->visit_type_label }}</td>
                </tr>
                <tr>
                    <td class="mc-cancel-table-label">Status:</td>
                    <td>
                        @if($appointment->status == 0)
                            <span class="mc-cancel-status mc-cancel-status-pending">Pending</span>
                        @elseif($appointment->status == 1)
                            <span class="mc-cancel-status mc-cancel-status-approved">Approved</span>
                        @elseif($appointment->status == 2)
                            <span class="mc-cancel-status mc-cancel-status-completed">Completed</span>
                        @else
                            <span class="mc-cancel-status mc-cancel-status-cancelled">Cancelled</span>
                        @endif
                    </td>
                </tr>
            </table>

            <div class="mc-cancel-actions">
                @if(in_array($appointment->status, [0, 1]))
                    <form action="{{ route('appointment.cancel-by-token', $appointment->cancellation_token) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="mc-cancel-btn">Cancel My Appointment</button>
                    </form>
                @else
                    <span class="mc-cancel-btn-disabled">This appointment can no longer be cancelled</span>
                @endif
            </div>

            <p class="mc-cancel-note">
                This link is unique to your appointment. Please keep it private.
            </p>

        </div>
    </div>
</section>

@endsection