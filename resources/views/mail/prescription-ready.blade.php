<x-mail::message>
# Hi {{ $prescription->patient_name }},

Your prescription from Dr. {{ $prescription->doctor->name ?? 'MediCare' }} is ready.

<x-mail::table>
| | |
| --- | --- |
| Doctor | Dr. {{ $prescription->doctor->name ?? 'N/A' }} |
| Date | {{ $prescription->created_at->format('d F Y') }} |

@if($prescription->diagnosis)
| Diagnosis | {{ $prescription->diagnosis }} |
@endif
@if($prescription->follow_up_date)
| Follow-up | {{ $prescription->follow_up_date->format('d F Y') }} |
@endif
</x-mail::table>

@if($prescription->items->count())
<x-mail::table>
| Medicine | Dose | Frequency | Duration |
| --- | --- | --- | --- |
@foreach($prescription->items as $item)
| {{ $item->medicine_name }} | {{ $item->dosage ?? '-' }} | {{ $item->frequency ?? '-' }} | {{ $item->duration ?? '-' }} |
@endforeach
</x-mail::table>
@endif

@if($prescription->advice)
**Doctor's advice:** {{ $prescription->advice }}
@endif

You can view and download the prescription from your **My Prescriptions** section on the MediCare site.

<x-mail::button :url="route('profile')">
View My Prescriptions
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>