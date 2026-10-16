@extends('frontend.layouts.front-app')

@section('meta_title', __('messages.public.cancel_title'))

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => __('messages.public.cancel_title')
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
            <h2 class="mc-cancel-header-title">{{ __('messages.public.cancel_details') }}</h2>
        </div>

        <div class="mc-cancel-body">

            <p class="mc-cancel-lead">
                {{ __('messages.public.cancel_greeting', ['name' => $appointment->patient_name]) }}
            </p>

            <table class="mc-cancel-table">
                <tr>
                    <td class="mc-cancel-table-label">{{ __('messages.mail_body.t_doctor') }}:</td>
                    <td>{{ $appointment->doctor->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="mc-cancel-table-label">{{ __('messages.mail_body.t_date') }}:</td>
                    <td>{{ $appointment->appointment_date }}</td>
                </tr>
                <tr>
                    <td class="mc-cancel-table-label">{{ __('messages.mail_body.t_time') }}:</td>
                    <td>{{ $appointment->timeSlot->time ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="mc-cancel-table-label">{{ __('messages.mail_body.t_visit_type') }}:</td>
                    <td>{{ $appointment->visit_type_label }}</td>
                </tr>
                <tr>
                    <td class="mc-cancel-table-label">{{ __('messages.common.status') }}:</td>
                    <td>
                        @if($appointment->status == 0)
                            <span class="mc-cancel-status mc-cancel-status-pending">{{ \Illuminate\Support\Str::ucfirst(__('messages.notifications.status_pending')) }}</span>
                        @elseif($appointment->status == 1)
                            <span class="mc-cancel-status mc-cancel-status-approved">{{ \Illuminate\Support\Str::ucfirst(__('messages.notifications.status_approved')) }}</span>
                        @elseif($appointment->status == 2)
                            <span class="mc-cancel-status mc-cancel-status-completed">{{ \Illuminate\Support\Str::ucfirst(__('messages.notifications.status_completed')) }}</span>
                        @else
                            <span class="mc-cancel-status mc-cancel-status-cancelled">{{ \Illuminate\Support\Str::ucfirst(__('messages.notifications.status_cancelled')) }}</span>
                        @endif
                    </td>
                </tr>
            </table>

            <div class="mc-cancel-actions">
                @if(in_array($appointment->status, [0, 1]))
                    <form action="{{ route('appointment.cancel-by-token', $appointment->cancellation_token) }}"
                          method="POST"
                          onsubmit="return confirm('{{ __('messages.public.cancel_confirm') }}');">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="mc-cancel-btn">{{ __('messages.mail_body.booked_cancel_button') }}</button>
                    </form>
                @else
                    <span class="mc-cancel-btn-disabled">{{ __('messages.public.cancel_closed') }}</span>
                @endif
            </div>

            <p class="mc-cancel-note">
                {{ __('messages.public.cancel_note') }}
            </p>

        </div>
    </div>
</section>

@endsection
