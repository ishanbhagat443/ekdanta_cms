<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; color: #222; max-width: 640px; margin: 0 auto;">
    @if (setting_image('branding.logo_email'))
        <img src="{{ setting_image('branding.logo_email') }}" alt="" style="max-height: 56px; margin: 16px 0;">
    @endif

    <h2 style="color: #111827;">New submission — {{ $form->name }}</h2>
    <p style="color: #666;">Received {{ $submission->created_at->format('d M Y, H:i') }} · IP {{ $submission->ip }}</p>

    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        @foreach ($form->fields as $field)
            <tr>
                <td style="border: 1px solid #ddd; background: #f8f8f8; width: 35%;"><strong>{{ $field->label }}</strong></td>
                <td style="border: 1px solid #ddd;">{{ $submission->valueFor($field->name) ?: '—' }}</td>
            </tr>
        @endforeach
    </table>

    <p style="color: #999; font-size: 12px; margin-top: 24px;">
        {{ setting('business.company_name', '') }} — automated notification.
    </p>
</body>
</html>
