<x-mail::message>
# Hi {{ $appointment->patient_name }},

Your appointment has been **confirmed**.

**Details:**

<x-mail::table>
| | |
| --- | --- |
| Doctor | {{ $appointment->doctor->name ?? 'N/A' }} |
| Date | {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, d F Y') }} |
| Time | {{ $appointment->timeSlot->time ?? 'N/A' }} |
| Visit Type | {{ $appointment->visit_type_label }} |
</x-mail::table>

Please arrive at least **15 minutes early** and carry any previous reports with you.

<x-mail::button :url="route('home')">
Visit MediCare
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>