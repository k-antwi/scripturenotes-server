<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Identity Verified</title>
</head>
<body style="font-family: sans-serif; color: #1a1a1a; background: #f9f9f9; padding: 32px;">
    <div style="max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #e5e5e5; border-radius: 8px; padding: 32px;">
        <h2 style="margin-top: 0; color: #166534;">Your identity has been verified ✓</h2>

        <p>
            Hi {{ $verification->user->first_name ?? $verification->user->name ?? 'there' }},
        </p>
        <p>
            We're pleased to let you know that your identity verification has been approved. You now have full access to your account.
        </p>

        <a href="{{ url('/dashboard') }}"
           style="display: inline-block; padding: 10px 20px; background: #1a1a1a; color: #fff; text-decoration: none; border-radius: 6px; font-size: 14px;">
            Go to your dashboard
        </a>

        <p style="margin-top: 32px; font-size: 12px; color: #9ca3af;">
            This is an automated notification. Please do not reply to this email.
        </p>
    </div>
</body>
</html>
