<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Action Required — Re-upload Documents</title>
</head>
<body style="font-family: sans-serif; color: #1a1a1a; background: #f9f9f9; padding: 32px;">
    <div style="max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #e5e5e5; border-radius: 8px; padding: 32px;">
        <h2 style="margin-top: 0;">Action required — please re-upload some documents</h2>

        <p>
            Hi {{ $verification->user->first_name ?? $verification->user->name ?? 'there' }},
        </p>
        <p>
            Our compliance team has reviewed your submission and needs additional information for the following check(s):
        </p>

        @foreach($verification->checksNeedingAttention() as $type => $check)
            <div style="border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px; margin: 12px 0;">
                <p style="margin: 0 0 8px; font-weight: 600;">{{ $check['label'] }}</p>
                @if($check['note'])
                    <p style="margin: 0 0 8px; font-size: 14px; color: #374151;">{{ $check['note'] }}</p>
                @endif
                <a href="{{ url($check['route']) }}"
                   style="font-size: 13px; color: #2563eb; text-decoration: underline;">
                    Re-upload {{ strtolower($check['label']) }} →
                </a>
            </div>
        @endforeach

        @if($verification->overall_review_note)
            <p style="margin-top: 16px; font-size: 14px; color: #6b7280;">
                <strong>General note:</strong> {{ $verification->overall_review_note }}
            </p>
        @endif

        <p>Once you have re-uploaded the required documents, you can re-submit your verification from your account page.</p>

        <p style="margin-top: 32px; font-size: 12px; color: #9ca3af;">
            This is an automated notification. Please do not reply to this email.
        </p>
    </div>
</body>
</html>
