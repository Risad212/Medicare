<x-mail::message>
# {{ __('messages.mail_body.greeting', ['name' => $prescription->patient_name]) }}

{{ __('messages.mail_body.rx_intro', ['doctor' => $prescription->doctor->name ?? 'MediCare']) }}

<x-mail::table>
| | |
| --- | --- |
| {{ __('messages.mail_body.t_doctor') }} | Dr. {{ $prescription->doctor->name ?? 'N/A' }} |
| {{ __('messages.mail_body.t_date') }} | {{ $prescription->created_at->format('d F Y') }} |

@if($prescription->diagnosis)
| {{ __('messages.mail_body.t_diagnosis') }} | {{ $prescription->diagnosis }} |
@endif
@if($prescription->follow_up_date)
| {{ __('messages.mail_body.t_followup') }} | {{ $prescription->follow_up_date->format('d F Y') }} |
@endif
</x-mail::table>

@if($prescription->items->count())
<x-mail::table>
| {{ __('messages.pdf.medicine') }} | {{ __('messages.pdf.dosage') }} | {{ __('messages.pdf.frequency') }} | {{ __('messages.pdf.duration') }} |
| --- | --- | --- | --- |
@foreach($prescription->items as $item)
| {{ $item->medicine_name }} | {{ $item->dosage ?? '-' }} | {{ $item->frequency ?? '-' }} | {{ $item->duration ?? '-' }} |
@endforeach
</x-mail::table>
@endif

@if($prescription->advice)
**{{ __('messages.mail_body.rx_advice') }}** {{ $prescription->advice }}
@endif

{{ __('messages.mail_body.rx_outro') }}

<x-mail::button :url="route('profile')">
{{ __('messages.mail_body.rx_view') }}
</x-mail::button>

{{ __('messages.mail_body.thanks') }}<br>
{{ config('app.name') }}
</x-mail::message>
