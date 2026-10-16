<div style="font-family: Arial, Helvetica, sans-serif; max-width: 650px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">

    <div style="background: #0f766e; padding: 25px; text-align: center;">
        <h2 style="color: #ffffff; margin: 0; font-size: 24px;">
            {{ __('messages.mail_body.booked_title') }}
        </h2>
    </div>

    <div style="padding: 30px;">

        <p style="font-size: 16px; color: #374151; line-height: 1.6;">
            {{ __('messages.mail_body.greeting', ['name' => $appointment->patient_name]) }}
        </p>

        <p style="font-size: 16px; color: #374151; line-height: 1.6;">
            {{ __('messages.mail_body.booked_intro') }}
        </p>

        <div style="margin: 25px 0; border-top: 1px solid #e5e7eb;"></div>

        <table width="100%" cellpadding="8" cellspacing="0" style="font-size: 15px; color: #374151;">
            <tr>
                <td style="font-weight: bold; width: 40%;">{{ __('messages.mail_body.t_doctor') }}:</td>
                <td>{{ $appointment->doctor->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">{{ __('messages.mail_body.t_date') }}:</td>
                <td>{{ $appointment->appointment_date }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">{{ __('messages.mail_body.t_time') }}:</td>
                <td>{{ $appointment->timeSlot->time ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">{{ __('messages.mail_body.t_visit_type') }}:</td>
                <td>{{ $appointment->visit_type_label }}</td>
            </tr>
        </table>

        <div style="margin: 25px 0; border-top: 1px solid #e5e7eb;"></div>

        <p style="font-size: 15px; color: #374151; line-height: 1.6;">
            {{ __('messages.mail_body.booked_cancel_intro') }}
        </p>

        <div style="text-align: center; margin: 25px 0;">
            <a href="{{ route('appointment.cancel-page', $appointment->cancellation_token) }}"
               style="background: #dc2626; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-size: 15px; display: inline-block; font-weight: bold;">
                {{ __('messages.mail_body.booked_cancel_button') }}
            </a>
        </div>

        <p style="font-size: 13px; color: #6b7280; text-align: center;">
            {{ __('messages.mail_body.booked_keep_safe') }}
        </p>

        <p style="font-size: 15px; color: #374151; margin-top: 25px;">
            {{ __('messages.mail_body.booked_regards') }}<br>
            <strong>{{ __('messages.mail_body.booked_team') }}</strong>
        </p>

    </div>

    <div style="background: #f3f4f6; padding: 15px; text-align: center; font-size: 13px; color: #6b7280;">
        {{ __('messages.mail_body.booked_auto') }}
    </div>

</div>
