<x-mail::message>
# Hi {{ $appointment->patient_name }},

A friendly reminder that your appointment is **tomorrow**.

**Details:**

<x-mail::table>
| | |
| --- | --- |
| Doctor | {{ $appointment->doctor->name ?? 'N/A' }} |
| Date | {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, d F Y') }} |
| Time | {{ $appointment->timeSlot->time ?? 'N/A' }} |
</x-mail::table>

Please arrive a little early and bring any previous reports.

<x-mail::button :url="route('home')">
Visit MediCare
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>