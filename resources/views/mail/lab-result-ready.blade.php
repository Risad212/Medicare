<x-mail::message>
# {{ __('messages.mail_body.greeting', ['name' => $labOrder->patient_name]) }}

{{ __('messages.mail_body.lab_intro') }}

<x-mail::table>
| | |
| --- | --- |
| {{ __('messages.mail_body.t_order_no') }} | {{ $labOrder->id }} |
| {{ __('messages.mail_body.t_requested_by') }} | Dr. {{ $labOrder->doctor->name ?? 'N/A' }} |
| {{ __('messages.mail_body.t_date') }} | {{ $labOrder->created_at->format('d F Y') }} |

@foreach($labOrder->items as $item)
| {{ $item->test->name ?? __('messages.mail_body.lab_test') }} | {{ $item->result ? __('messages.mail_body.reported') : __('messages.mail_body.pending') }} |
@endforeach
</x-mail::table>

{{ __('messages.mail_body.lab_outro') }}

<x-mail::button :url="route('profile')">
{{ __('messages.mail_body.lab_view') }}
</x-mail::button>

{{ __('messages.mail_body.thanks') }}<br>
{{ config('app.name') }}
</x-mail::message>
