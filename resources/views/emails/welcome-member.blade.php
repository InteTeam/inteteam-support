<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to OOR-HQ</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f4f5; margin: 0; padding: 24px; }
        .wrapper { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; }
        .header { background: #18181b; padding: 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; }
        .body { padding: 32px; color: #3f3f46; line-height: 1.6; }
        .body h2 { color: #18181b; font-size: 20px; margin-top: 0; }
        .credentials { background: #f4f4f5; border-radius: 6px; padding: 16px 20px; margin: 24px 0; }
        .credentials p { margin: 6px 0; font-size: 14px; }
        .credentials strong { color: #18181b; }
        .btn { display: inline-block; background: #18181b; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; margin: 8px 0; }
        .footer { padding: 20px 32px; border-top: 1px solid #e4e4e7; font-size: 12px; color: #a1a1aa; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>OOR-HQ</h1>
        </div>
        <div class="body">
            <h2>Welcome, {{ $user->name }}!</h2>
            <p>Your OOR-HQ account has been created. You now have your own headquarters: <strong>{{ $hqName }}</strong>.</p>
            <p>Use the credentials below to log in for the first time. Please change your password after logging in.</p>

            <div class="credentials">
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Temporary Password:</strong> {{ $temporaryPassword }}</p>
            </div>

            <a href="{{ $loginUrl }}" class="btn">Log in to OOR-HQ</a>

            <p style="margin-top: 24px; font-size: 14px; color: #71717a;">
                If you have any questions, contact your administrator.
            </p>
        </div>
        <div class="footer">
            OOR-HQ &mdash; Our HQ &mdash; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
