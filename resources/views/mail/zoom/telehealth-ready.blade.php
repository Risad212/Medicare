<x-mail::message>
# Your telehealth consultation is ready

Your approved online consultation is scheduled for:

**{{ $appointment->appointment_date->format('l, d F Y') }} at {{ $appointment->timeSlot->time ?? 'the scheduled time' }}**

The private link is available shortly before the scheduled time and expires afterward. Do not forward it.

<x-mail::button :url="$joinUrl">
Join consultation
</x-mail::button>

If you did not request this consultation, contact the clinic.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
