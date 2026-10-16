<x-mail::message>
# {{ __('messages.mail_body.greeting', ['name' => $appointment->patient_name]) }}

{{ __('messages.mail_body.confirmed_intro') }}

**{{ __('messages.mail_body.details') }}**

<x-mail::table>
| | |
| --- | --- |
| {{ __('messages.mail_body.t_doctor') }} | {{ $appointment->doctor->name ?? 'N/A' }} |
| {{ __('messages.mail_body.t_date') }} | {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, d F Y') }} |
| {{ __('messages.mail_body.t_time') }} | {{ $appointment->timeSlot->time ?? 'N/A' }} |
| {{ __('messages.mail_body.t_visit_type') }} | {{ $appointment->visit_type_label }} |
</x-mail::table>

@if($appointment->consultation_mode === 'telehealth')
{{ __('messages.mail_body.confirmed_telehealth') }}
@else
{{ __('messages.mail_body.confirmed_inperson') }}
@endif

<x-mail::button :url="route('home')">
{{ __('messages.mail_body.visit_site') }}
</x-mail::button>

{{ __('messages.mail_body.thanks') }}<br>
{{ config('app.name') }}
</x-mail::message>
