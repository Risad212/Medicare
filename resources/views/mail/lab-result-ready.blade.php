<x-mail::message>
# Hi {{ $labOrder->patient_name }},

Your lab results are **ready**!

<x-mail::table>
| | |
| --- | --- |
| Order # | {{ $labOrder->id }} |
| Requested by | Dr. {{ $labOrder->doctor->name ?? 'N/A' }} |
| Date | {{ $labOrder->created_at->format('d F Y') }} |

@foreach($labOrder->items as $item)
| {{ $item->test->name ?? 'Lab test' }} | {{ $item->result ? 'Reported' : 'Pending' }} |
@endforeach
</x-mail::table>

You can view and download the reports from your **My Lab Reports** section on the MediCare site.

<x-mail::button :url="route('profile')">
View My Reports
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>