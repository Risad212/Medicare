<div style="font-family: Arial, Helvetica, sans-serif; max-width: 650px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">

    <div style="background: #0f766e; padding: 25px; text-align: center;">
        <h2 style="color: #ffffff; margin: 0; font-size: 24px;">
            New Contact Inquiry Received
        </h2>
    </div>

    <div style="padding: 30px;">

        <p style="font-size: 16px; color: #374151;">
            Hello Hospital Team,
        </p>

        <p style="font-size: 16px; color: #374151; line-height: 1.6;">
            A new inquiry has been submitted through your website contact form.
            Please review the details below and respond to the visitor as soon as possible.
        </p>

        <div style="margin: 25px 0; border-top: 1px solid #e5e7eb;"></div>

        <table width="100%" cellpadding="8" cellspacing="0" style="font-size: 15px; color: #374151;">
            <tr>
                <td style="font-weight: bold; width: 35%;">Patient/Visitor Name:</td>
                <td>{{ $data['name'] }}</td>
            </tr>

            <tr>
                <td style="font-weight: bold;">Email Address:</td>
                <td>
                    <a href="mailto:{{ $data['email'] }}" style="color: #0f766e;">
                        {{ $data['email'] }}
                    </a>
                </td>
            </tr>

            <tr>
                <td style="font-weight: bold;">Phone Number:</td>
                <td>{{ $data['phone'] }}</td>
            </tr>

            <tr>
                <td style="font-weight: bold;">Subject:</td>
                <td>{{ $data['subject'] }}</td>
            </tr>
        </table>

        <div style="margin: 25px 0; border-top: 1px solid #e5e7eb;"></div>

        <p style="font-weight: bold; color: #374151;">
            Message:
        </p>

        <div style="background: #f9fafb; padding: 15px; border-radius: 6px; color: #374151; line-height: 1.6;">
            {{ $data['message'] }}
        </div>

        <div style="margin: 30px 0 20px;">
            <p style="font-size: 15px; color: #374151;">
                Please follow up with the sender regarding this inquiry.
            </p>
        </div>

        <p style="font-size: 15px; color: #374151;">
            Regards,<br>
            <strong>Hospital Website Team</strong>
        </p>

    </div>

    <div style="background: #f3f4f6; padding: 15px; text-align: center; font-size: 13px; color: #6b7280;">
        This email was generated automatically from your hospital website contact form.
    </div>

</div>