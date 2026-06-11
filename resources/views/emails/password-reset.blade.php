<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5; }
        .container { background: #fff; border-radius: 8px; padding: 32px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #3B82F6; padding-bottom: 16px; }
        .header h1 { color: #3B82F6; margin: 0; font-size: 24px; }
        .content { margin: 24px 0; }
        .content p { margin: 16px 0; color: #374151; }
        .btn-container { text-align: center; margin: 32px 0; }
        .reset-btn { display: inline-block; background: #3B82F6; color: #fff !important; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-weight: 600; font-size: 16px; }
        .reset-btn:hover { background: #2563EB; }
        .notice { background: #FEF3C7; border: 1px solid #FCD34D; border-radius: 6px; padding: 12px 16px; margin: 24px 0; font-size: 14px; color: #92400E; }
        .link-fallback { margin: 24px 0; padding: 16px; background: #F3F4F6; border-radius: 6px; font-size: 13px; word-break: break-all; }
        .link-fallback p { margin: 0 0 8px 0; color: #6B7280; }
        .link-fallback a { color: #3B82F6; }
        .footer { text-align: center; margin-top: 32px; padding-top: 16px; border-top: 1px solid #E2E8F0; color: #64748B; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $company->name }}</h1>
        </div>

        <div class="content">
            <p>Hello {{ $user->name }},</p>

            <p>You are receiving this email because we received a password reset request for your account.</p>

            <div class="btn-container">
                <a href="{{ $resetUrl }}" class="reset-btn">Reset Password</a>
            </div>

            <p>This password reset link will expire in {{ $expireMinutes }} minutes.</p>

            <div class="notice">
                If you did not request a password reset, no further action is required. Your password will remain unchanged.
            </div>

            <div class="link-fallback">
                <p>If you're having trouble clicking the button, copy and paste the URL below into your web browser:</p>
                <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ $company->name }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
