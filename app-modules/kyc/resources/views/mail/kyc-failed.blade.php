<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verification Unsuccessful</title>
</head>
<body style="font-family: sans-serif; color: #1a1a1a; background: #f9f9f9; padding: 32px;">
    <div style="max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #e5e5e5; border-radius: 8px; padding: 32px;">
        <h2 style="margin-top: 0; color: #991b1b;">Verification unsuccessful</h2>

        <p>
            Hi {{ $verification->user->first_name ?? $verification->user->name ?? 'there' }},
        </p>
        <p>
            Unfortunately, we were unable to verify your identity at this time. Our compliance team has reviewed your submission and reached the following decision:
        </p>

        @if($verification->overall_review_note)
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 16px; margin: 16px 0;">
                <p style="margin: 0; font-size: 14px;">{{ $verification->overall_review_note }}</p>
            </div>
        @endif

        <p>
            If you believe this decision is incorrect, please contact our support team.
        </p>

        <p style="margin-top: 32px; font-size: 12px; color: #9ca3af;">
            This is an automated notification. Please do not reply to this email.
        </p>
    </div>
</body>
</html>
