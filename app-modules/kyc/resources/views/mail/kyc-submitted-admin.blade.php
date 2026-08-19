<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New KYC Submission</title>
</head>
<body style="font-family: sans-serif; color: #1a1a1a; background: #f9f9f9; padding: 32px;">
    <div style="max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #e5e5e5; border-radius: 8px; padding: 32px;">
        <h2 style="margin-top: 0;">New KYC Submission — Action Required</h2>

        <p>A new KYC verification has been submitted and is ready for review.</p>

        <table style="width: 100%; border-collapse: collapse; margin: 24px 0;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; width: 140px;">User</td>
                <td style="padding: 8px 0;">{{ $verification->user->name ?? $verification->user->email }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Email</td>
                <td style="padding: 8px 0;">{{ $verification->user->email }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Submitted at</td>
                <td style="padding: 8px 0;">{{ $verification->submitted_at?->format('d M Y, H:i') ?? 'just now' }}</td>
            </tr>
        </table>

        <a href="{{ url('/admin/kyc-verifications/' . $verification->id . '/edit') }}"
           style="display: inline-block; padding: 10px 20px; background: #1a1a1a; color: #fff; text-decoration: none; border-radius: 6px; font-size: 14px;">
            Review submission
        </a>

        <p style="margin-top: 32px; font-size: 12px; color: #9ca3af;">
            This is an automated notification. Please do not reply to this email.
        </p>
    </div>
</body>
</html>
